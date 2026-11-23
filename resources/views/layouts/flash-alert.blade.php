{{--
    Flash alert standar KasirAja (gaya sama dengan alert session('success') / session('error') di halaman CRUD).
    Dipakai untuk notifikasi dari JavaScript: showFlash(judul, pesan, 'success' | 'error').
--}}
<div id="flash-alert-stack" class="fixed top-4 right-4 z-[60] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm pointer-events-none" aria-live="polite"></div>

<script>
    window.showFlash = function (title, message, type = 'success') {
        const stack = document.getElementById('flash-alert-stack');
        if (!stack) return;

        const isSuccess = type === 'success';
        const alert = document.createElement('div');
        alert.setAttribute('role', isSuccess ? 'status' : 'alert');
        alert.className = 'pointer-events-auto flex items-start justify-between gap-3 p-4 border rounded-xl text-sm shadow-sm transition-all duration-300 '
            + (isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800');

        const icon = isSuccess
            ? '<svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
            : '<svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';

        const body = document.createElement('div');
        body.className = 'flex items-start gap-2 min-w-0';
        body.innerHTML = icon;
        const text = document.createElement('div');
        text.className = 'min-w-0';
        const strong = document.createElement('div');
        strong.className = 'font-semibold';
        strong.textContent = title;
        const msg = document.createElement('div');
        msg.className = 'text-xs mt-0.5 opacity-90 break-words';
        msg.textContent = message;
        text.append(strong, msg);
        body.appendChild(text);

        const close = document.createElement('button');
        close.type = 'button';
        close.className = (isSuccess ? 'text-emerald-600 hover:text-emerald-900' : 'text-red-600 hover:text-red-900') + ' font-bold leading-none cursor-pointer';
        close.innerHTML = '&times;';
        close.addEventListener('click', () => alert.remove());

        alert.append(body, close);
        stack.appendChild(alert);

        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    };
</script>
