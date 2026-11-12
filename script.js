document.addEventListener('DOMContentLoaded', () => {
    // Live search filter enhancement
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.brut-card');

            // Skip the filter form card itself, check grid item cards
            cards.forEach(card => {
                const titleEl = card.querySelector('h3');
                if (titleEl) {
                    const text = card.textContent.toLowerCase();
                    if (text.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    }

    // Image upload preview
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (evt) {
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'image-preview';
                        preview.className = 'mt-3 h-32 w-32 object-cover rounded-xl border border-slate-200 shadow-sm';
                        fileInput.parentNode.appendChild(preview);
                    }
                    preview.src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Smooth scroll for internal navbar links
    const navLinks = document.querySelectorAll('nav a[href*="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.includes('index.php#search-section') && window.location.pathname.endsWith('index.php') || window.location.pathname === '/') {
                e.preventDefault();
                const target = document.getElementById('search-section');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Smooth scroll to top when clicking Beranda on index
    const berandaLinks = document.querySelectorAll('nav a');
    berandaLinks.forEach(link => {
        if (link.textContent.trim() === 'Beranda' && (window.location.pathname.endsWith('index.php') || window.location.pathname === '/')) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
    const cards = document.querySelectorAll('.brut-card');
    cards.forEach(card => card.classList.add('scroll-reveal'));

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });

    cards.forEach(card => observer.observe(card));
});
