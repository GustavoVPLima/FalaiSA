// theme-toggle.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar tema salvo no localStorage
    const savedTheme = localStorage.getItem('theme');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Função para aplicar o tema
    function applyTheme(theme) {
        if (theme === 'dark' || (!theme && prefersDarkScheme.matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }
    
    // Aplicar tema inicial
    applyTheme(savedTheme);
    
    // Evento para mudar tema (será chamado dos botões)
    window.toggleTheme = function() {
        const currentTheme = localStorage.getItem('theme');
        let newTheme;
        
        if (currentTheme === 'light') {
            newTheme = 'dark';
        } else {
            newTheme = 'light';
        }
        
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