function debounce(func, wait = 300) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function initCourseSearch(endpointUrl, tableBodyId, isStudentEnlistment = false) {
    const searchInput = document.getElementById('course-search-input');
    const deptFilter = document.getElementById('course-dept-filter');
    const tbody = document.getElementById(tableBodyId);

    if (!tbody) return;

    const performSearch = async () => {
        const query = searchInput ? searchInput.value.trim() : '';
        const dept = deptFilter ? deptFilter.value : '';

        try {
            const res = await fetch(`${endpointUrl}?q=${encodeURIComponent(query)}&department=${encodeURIComponent(dept)}`);
            const data = await res.json();

            if (data.status === 'success') {
                renderCourses(data.data, tbody, isStudentEnlistment);
            }
        } catch (err) {
            console.error('Course search error:', err);
        }
    };

    if (searchInput) searchInput.addEventListener('input', debounce(performSearch, 250));
    if (deptFilter) deptFilter.addEventListener('change', performSearch);
}

function renderCourses(courses, tbody, isStudentEnlistment) {
    if (courses.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                    <p class="font-medium">No courses found matching your criteria</p>
                </td>
            </tr>`;
        return;
    }

    let html = '';
    courses.forEach(c => {
        const isFull = parseInt(c.enrolled_count) >= parseInt(c.capacity);
        const capacityBadge = isFull 
            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-700">Full (${c.enrolled_count}/${c.capacity})</span>`
            : `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">${c.enrolled_count}/${c.capacity}</span>`;

        if (isStudentEnlistment) {
            html += `
                <tr class="hover:bg-slate-50 transition border-b border-slate-100" id="course-row-${c.id}">
                    <td class="px-5 py-4 font-bold text-brand-600">${c.course_code}</td>
                    <td class="px-5 py-4 font-semibold text-slate-800">${c.course_title}</td>
                    <td class="px-5 py-4 text-slate-600">${c.units} units</td>
                    <td class="px-5 py-4 text-slate-600">${c.schedule} <br><span class="text-xs text-slate-400">${c.room}</span></td>
                    <td class="px-5 py-4 text-slate-600">${c.instructor}</td>
                    <td class="px-5 py-4">${capacityBadge}</td>
                    <td class="px-5 py-4 text-right">
                        ${isFull 
                            ? `<button disabled class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-200 text-slate-400 cursor-not-allowed">Class Full</button>`
                            : `<button onclick="handleEnroll(${c.id})" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition">
                                 <i class="fa-solid fa-plus mr-1"></i>Enroll
                               </button>`
                        }
                    </td>
                </tr>`;
        } else {
            html += `
                <tr class="hover:bg-slate-50 transition border-b border-slate-100" id="course-row-${c.id}">
                    <td class="px-5 py-4 font-bold text-brand-600">${c.course_code}</td>
                    <td class="px-5 py-4">
                        <div class="font-semibold text-slate-900">${c.course_title}</div>
                        <div class="text-xs text-slate-500">${c.department} &bull; ${c.semester}</div>
                    </td>
                    <td class="px-5 py-4 text-slate-700 font-medium">${c.units}</td>
                    <td class="px-5 py-4 text-slate-600 text-xs">${c.schedule}<br><span class="text-slate-400">${c.room}</span></td>
                    <td class="px-5 py-4 text-slate-700">${c.instructor}</td>
                    <td class="px-5 py-4">${capacityBadge}</td>
                    <td class="px-5 py-4 text-right space-x-2">
                        <button onclick="editCourseModal(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="text-brand-600 hover:text-brand-900 text-xs font-semibold p-1.5 rounded hover:bg-brand-50" title="Edit Subject">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form method="POST" action="courses.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this course? Enrolled students will also be affected.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="${c.id}">
                            <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold p-1.5 rounded hover:bg-rose-50" title="Delete Subject">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>`;
        }
    });
    tbody.innerHTML = html;
}

function initStudentSearch(endpointUrl, tableBodyId) {
    const searchInput = document.getElementById('student-search-input');
    const courseFilter = document.getElementById('student-course-filter');
    const yearFilter = document.getElementById('student-year-filter');
    const tbody = document.getElementById(tableBodyId);

    if (!tbody) return;

    const performSearch = async () => {
        const query = searchInput ? searchInput.value.trim() : '';
        const course = courseFilter ? courseFilter.value : '';
        const year = yearFilter ? yearFilter.value : '';

        try {
            const res = await fetch(`${endpointUrl}?q=${encodeURIComponent(query)}&course=${encodeURIComponent(course)}&year_level=${encodeURIComponent(year)}`);
            const data = await res.json();

            if (data.status === 'success') {
                renderStudents(data.data, tbody);
            }
        } catch (err) {
            console.error('Student search error:', err);
        }
    };

    if (searchInput) searchInput.addEventListener('input', debounce(performSearch, 250));
    if (courseFilter) courseFilter.addEventListener('change', performSearch);
    if (yearFilter) yearFilter.addEventListener('change', performSearch);
}

function renderStudents(students, tbody) {
    if (students.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                    <p class="font-medium">No students found matching your criteria</p>
                </td>
            </tr>`;
        return;
    }

    let html = '';
    students.forEach(s => {
        const fullName = `${s.first_name} ${s.middle_name ? s.middle_name[0] + '. ' : ''}${s.last_name}`;
        html += `
            <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                <td class="px-5 py-4 font-mono font-bold text-brand-600">${s.student_id_number}</td>
                <td class="px-5 py-4">
                    <div class="font-bold text-slate-900">${fullName}</div>
                    <div class="text-xs text-slate-500">${s.email}</div>
                </td>
                <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-brand-50 text-brand-700">${s.course || 'N/A'}</span>
                </td>
                <td class="px-5 py-4 text-slate-700 text-sm">
                    ${s.year_level || 'N/A'} <span class="text-xs text-slate-400">(${s.section || 'N/A'})</span>
                </td>
                <td class="px-5 py-4 text-xs font-mono text-slate-600">${s.contact_number || 'None'}</td>
                <td class="px-5 py-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        ${s.enrolled_subjects} subjects
                    </span>
                </td>
                <td class="px-5 py-4 text-right space-x-1">
                    <button onclick="editStudentModal(${JSON.stringify(s).replace(/"/g, '&quot;')})" class="text-brand-600 hover:text-brand-900 text-xs font-semibold p-1.5 rounded hover:bg-brand-50" title="Edit Student">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <form method="POST" action="students.php" class="inline" onsubmit="return confirm('Are you sure you want to delete student ${fullName}? All grades and enrollments will also be removed.')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="${s.id}">
                        <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold p-1.5 rounded hover:bg-rose-50" title="Delete Student">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </td>
            </tr>`;
    });
    tbody.innerHTML = html;
}
