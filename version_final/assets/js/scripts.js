document.addEventListener('DOMContentLoaded', function () {
    const formulaire = document.querySelector('form');
    if (formulaire) {
        formulaire.addEventListener('submit', function (e) {
            const identifiant = document.querySelector('input[name="id"]').value;
            const motDePasse = document.querySelector('input[name="mot_de_passe"]').value;
            const role = document.querySelector('select[name="role"]').value;

            if (!identifiant || !motDePasse || !role) {
                e.preventDefault();
                alert('Tous les champs sont obligatoires.');
            }
        });
    }

    const boutonsDeconnexion = document.querySelectorAll('.logout-btn');
    boutonsDeconnexion.forEach(bouton => {
        bouton.addEventListener('click', function (e) {
            if (!confirm('Voulez-vous vraiment vous déconnecter ?')) {
                e.preventDefault();
                return;
            }
        });
    });

    function animerElements() {
        const elements = document.querySelectorAll('.animate-on-load, .recherche-container, .auth-container, .site-title, table');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100); // Délai progressif pour un effet naturel
        });
    }

    window.addEventListener('scroll', function () {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top >= 0 && rect.top <= window.innerHeight) {
                element.style.opacity = '0';
                element.style.transform = 'translateY(50px)';
                element.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                    element.classList.remove('animate-on-scroll');
                }, 100);
            }
        });
    });

    function configurerTransition() {
        let superpositionTransition = document.querySelector('.transition-overlay');
        if (!superpositionTransition) {
            superpositionTransition = document.createElement('div');
            superpositionTransition.className = 'transition-overlay';
            document.body.appendChild(superpositionTransition);
        }

        const formulaire = document.querySelector('form');
        if (formulaire) {
            formulaire.addEventListener('submit', function (e) {
                if (!e.defaultPrevented) { // Si la validation passe
                    superpositionTransition.classList.add('active');
                    // Redirection après la fin de l'animation
                    setTimeout(() => {
                        superpositionTransition.classList.remove('active');
                    }, 4000); // Durée totale de 4 secondes
                }
            });
        }

        superpositionTransition.classList.remove('active');
    }

    animerElements();
    configurerTransition();
});
