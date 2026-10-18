function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColors = {
        success: 'bg-slate-900 text-white border-emerald-500',
        error: 'bg-rose-900 text-white border-rose-500',
        info: 'bg-indigo-900 text-white border-indigo-500',
        warning: 'bg-amber-900 text-white border-amber-500'
    };

    const icons = {
        success: '<i class="fa-solid fa-circle-check text-emerald-400 mr-2"></i>',
        error: '<i class="fa-solid fa-circle-exclamation text-rose-400 mr-2"></i>',
        info: '<i class="fa-solid fa-circle-info text-indigo-400 mr-2"></i>',
        warning: '<i class="fa-solid fa-triangle-exclamation text-amber-400 mr-2"></i>'
    };

    toast.className = `pointer-events-auto flex items-center px-4 py-3 rounded-xl border shadow-xl text-sm font-medium transition-all transform translate-y-4 opacity-0 ${bgColors[type] || bgColors.info}`;
    toast.innerHTML = `
        <div class="flex items-center">
            ${icons[type] || ''}
            <span>${message}</span>
        </div>
        <button class="ml-4 text-slate-400 hover:text-white" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
    }, 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop:not(.hidden)').forEach(modal => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    }
});

function recalculateGradeAverage() {
    const pInput = document.getElementById('modal-prelim');
    const mInput = document.getElementById('modal-midterm');
    const sInput = document.getElementById('modal-semifinal');
    const fInput = document.getElementById('modal-final');
    const avgDisplay = document.getElementById('modal-calculated-avg');
    const remarksBadge = document.getElementById('modal-remarks-badge');

    if (!avgDisplay) return;

    const values = [pInput, mInput, sInput, fInput]
        .map(input => (input && input.value !== '' && !isNaN(input.value)) ? parseFloat(input.value) : null)
        .filter(v => v !== null);

    if (values.length === 0) {
        avgDisplay.textContent = '--';
        if (remarksBadge) {
            remarksBadge.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Not Graded</span>';
        }
        return;
    }

    const sum = values.reduce((a, b) => a + b, 0);
    const avg = (sum / values.length).toFixed(2);
    avgDisplay.textContent = avg + '%';

    if (remarksBadge) {
        if (values.length === 4) {
            if (avg >= 75.00) {
                remarksBadge.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-check mr-1"></i>Passed</span>';
            } else {
                remarksBadge.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300"><i class="fa-solid fa-xmark mr-1"></i>Failed</span>';
            }
        } else {
            remarksBadge.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"><i class="fa-regular fa-clock mr-1"></i>In Progress (' + values.length + '/4 terms)</span>';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const passInput = document.getElementById('reg-password');
    const confirmInput = document.getElementById('reg-password-confirm');
    const matchFeedback = document.getElementById('password-match-feedback');

    if (passInput && confirmInput && matchFeedback) {
        const checkMatch = () => {
            if (confirmInput.value.length === 0) {
                matchFeedback.textContent = '';
                return;
            }
            if (passInput.value === confirmInput.value) {
                matchFeedback.textContent = 'Passwords match';
                matchFeedback.className = 'text-xs font-medium text-emerald-600 mt-1';
            } else {
                matchFeedback.textContent = 'Passwords do not match';
                matchFeedback.className = 'text-xs font-medium text-rose-600 mt-1';
            }
        };
        passInput.addEventListener('input', checkMatch);
        confirmInput.addEventListener('input', checkMatch);
    }

    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            if (val > 100) e.target.value = 100;
            if (val < 0) e.target.value = 0;
            recalculateGradeAverage();
        });
    });
});
