document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    const body = document.body;

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.contains('open');
            
            // Toggle das classes
            toggle.classList.toggle('open');
            navLinks.classList.toggle('open');
            
            // Atualiza acessibilidade
            toggle.setAttribute('aria-expanded', !isOpen);

            // Impede o scroll do fundo quando o menu está aberto
            if (!isOpen) {
                body.style.overflow = 'hidden';
            } else {
                body.style.overflow = '';
            }
        });

        // Fechar o menu ao clicar num link (importante para SPAs ou âncoras)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                toggle.classList.remove('open');
                navLinks.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                body.style.overflow = '';
            });
        });
    }
});