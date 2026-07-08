// theme-toggle.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar tema salvo no localStorage
    const savedTheme = localStorage.getItem('theme');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Função para aplicar o tema
    function applyTheme(theme) {
        const resolvedTheme = theme === 'dark' || (!theme && prefersDarkScheme.matches) ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', resolvedTheme);
        document.body.setAttribute('data-theme', resolvedTheme);
        document.documentElement.className = 'theme-' + resolvedTheme;
    }
    
    // Aplicar tema inicial ANTES de tudo
    const initialTheme = savedTheme || (prefersDarkScheme.matches ? 'dark' : 'light');
    const resolvedInitialTheme = initialTheme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', resolvedInitialTheme);
    document.body.setAttribute('data-theme', resolvedInitialTheme);
    document.documentElement.className = 'theme-' + resolvedInitialTheme;
    if (savedTheme) localStorage.setItem('theme', resolvedInitialTheme);
    
    // Aplicar tema inicial após evento
    applyTheme(savedTheme);
    
    // Evento para mudar tema (será chamado dos botões)
    window.toggleTheme = function() {
        const currentTheme = document.documentElement.getAttribute('data-theme') ||
            localStorage.getItem('theme') ||
            (prefersDarkScheme.matches ? 'dark' : 'light');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
        
        // Atualizar ícone do botão
        updateThemeButton();
        
        // Mostrar feedback
        showThemeNotification(newTheme);
    };
    
    // Função para atualizar o botão de alternância
    function updateThemeButton() {
        const buttons = document.querySelectorAll('.theme-toggle-btn');
        const currentTheme = localStorage.getItem('theme') || 
                            (prefersDarkScheme.matches ? 'dark' : 'light');
        
        buttons.forEach(button => {
            const icon = button.querySelector('.theme-icon');
            const text = button.querySelector('.theme-text');
            
            if (currentTheme === 'dark') {
                if (text) text.textContent = 'Modo Claro';
            } else {
                if (text) text.textContent = 'Modo Escuro';
            }
        });
    }
    
    // Função para mostrar notificação
    function showThemeNotification(theme) {
        // Remover notificação anterior se existir
        const existingNotification = document.querySelector('.theme-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Criar nova notificação
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <span>Modo ${theme === 'dark' ? 'Escuro' : 'Claro'} ativado</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remover após 3 segundos
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Chamar inicialmente para configurar botões
    updateThemeButton();
    
    // Listener para mudança de preferência do sistema
    prefersDarkScheme.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
            updateThemeButton();
        }
    });
});