// Atualize o arquivo dados_loader.js para este conteúdo:
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todos os itens de configuração
    const configOptions = document.querySelectorAll('.config-option');
    const contentArea = document.getElementById('modal_content_area');
    
    // Função para carregar conteúdo
    function loadContent(route, clickedElement) {
        // Remove a classe 'active' de todas as opções
        configOptions.forEach(option => {
            option.classList.remove('active');
        });
        
        // Adiciona a classe 'active' na opção clicada
        if (clickedElement) {
            clickedElement.classList.add('active');
        }
        
        // Mostra indicador de carregamento
        contentArea.innerHTML = '<div class="loading">Carregando...</div>';
        
        // Faz a requisição
        fetch(route)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar conteúdo');
                }
                return response.text();
            })
            .then(html => {
                contentArea.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro:', error);
                contentArea.innerHTML = `
                    <div class="error-message">
                        <p>Erro ao carregar conteúdo. Tente novamente.</p>
                    </div>
                `;
            });
    }
    
    // Adiciona evento de clique em cada opção
    configOptions.forEach(option => {
        option.addEventListener('click', function(event) {
            event.preventDefault();
            
            const route = this.getAttribute('data-route');
            loadContent(route, this);
        });
    });
    
    // Opcional: Carregar primeira opção automaticamente
    // Se você quiser que uma opção seja carregada por padrão
    // configOptions[0].click();
});