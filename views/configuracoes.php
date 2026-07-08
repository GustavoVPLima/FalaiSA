<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Configurações</h1>
</div>

<div class="form-container" style="max-width: 650px;">
    <div class="form-section">
        <div class="form-section-title">Preferências</div>

        <div class="modal_content_area">
            <div class="default-message">
                <p style="margin-bottom: 12px;">Tema do site:</p>

                <div style="display:flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                    <button type="button" class="btn btn-primary" onclick="window.toggleTheme && window.toggleTheme()">
                        Alternar tema
                    </button>

                    <span style="color: var(--text-gray); font-size: 0.95rem; align-self: center;">
                        (O estado do tema é salvo no seu navegador.)
                    </span>
                </div>
            </div>

            <div class="mt-2" style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
                <p style="color: var(--text-gray); font-size: 0.95rem;">
                    Em breve: outras configurações da conta.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>

