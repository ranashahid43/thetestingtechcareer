// validation.js
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        // Basic client-side checks
        const firstName = (formData.get('first_name') || '').trim();
        const lastName  = (formData.get('last_name') || '').trim();
        const email     = (formData.get('email') || '').trim();

        if (firstName.length < 2 || lastName.length < 2) {
            alert('Name fields must be at least 2 characters.');
            return;
        }
        if (!/^[^@]+@[^@]+\.[^@]+$/.test(email)) {
            alert('Please enter a valid email.');
            return;
        }

        // Additional quick checks for careers page
        if (form.action.includes('careers.php')) {
            if ((formData.get('cover_letter') || '').trim().length < 50) {
                alert('Cover letter must be at least 50 characters.');
                return;
            }
            if (!formData.get('cv')) {
                alert('Please upload your CV.');
                return;
            }
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = 'thankyou.html'; // Redirect to thank you page
            } else {
                alert(data.message || 'Something went wrong.');
            }
        } catch (err) {
            alert('Network error. Please try again.');
        }
    });
});