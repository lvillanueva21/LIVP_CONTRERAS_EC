<section class="section-page" id="section-personalizacion">
    <div class="page-title">
        <div>
            <span class="eyebrow">Configuración visual</span>
            <h2>Personalización</h2>
            <p>
                Aquí se configurarán logo, colores, datos del estudio contable y textos del recibo.
            </p>
        </div>

        <button type="button" class="btn btn--primary" id="btnGuardarPersonalizacionDemo">
            Guardar demo
        </button>
    </div>

    <div class="form-layout">
        <article class="panel">
            <div class="panel__header">
                <div>
                    <h3>Datos del estudio</h3>
                    <p>Datos iniciales temporales para el encabezado del recibo.</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre comercial</label>
                    <input type="text" class="form-control" value="Estudio Contable Contreras">
                </div>

                <div class="form-group">
                    <label>RUC</label>
                    <input type="text" class="form-control" value="10730652441">
                </div>

                <div class="form-group">
                    <label>Razón social</label>
                    <input type="text" class="form-control" value="MIRTHA VETTY BACA CONTRERAS">
                </div>

                <div class="form-group">
                    <label>Rubro de la empresa</label>
                    <input type="text" class="form-control" value="Asesoría contable y tributaria">
                </div>

                <div class="form-group form-group--full">
                    <label>Dirección</label>
                    <input type="text" class="form-control" value="CALLE MARTINEZ DE CAMPAÑON 911">
                </div>

                <div class="form-group">
                    <label>Color principal</label>
                    <input type="color" class="form-control form-control--color" value="#0f766e">
                </div>

                <div class="form-group">
                    <label>Color secundario</label>
                    <input type="color" class="form-control form-control--color" value="#14b8a6">
                </div>
            </div>
        </article>

        <article class="receipt-preview-mini">
            <div class="receipt-preview-mini__header">
                <div class="receipt-logo-demo">ECC</div>
                <div>
                    <h3>Estudio Contable Contreras</h3>
                    <p>Generador de recibos personalizable</p>
                </div>
            </div>

            <div class="receipt-preview-mini__body">
                <div>
                    <span>RUC</span>
                    <strong>10730652441</strong>
                </div>
                <div>
                    <span>Razón social</span>
                    <strong>MIRTHA VETTY BACA CONTRERAS</strong>
                </div>
                <div>
                    <span>Dirección</span>
                    <strong>CALLE MARTINEZ DE CAMPAÑON 911</strong>
                </div>
            </div>
        </article>
    </div>
</section>