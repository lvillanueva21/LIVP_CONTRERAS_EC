<section class="section-page" id="section-personalizacion">
    <form id="formPersonalizacionEstudio">
        <div class="page-title">
            <div>
                <span class="eyebrow">Configuración visual</span>
                <h2>Personalización</h2>
                <p>
                    Aquí se configurarán logo, colores, datos del estudio contable y textos del recibo.
                </p>
            </div>

            <div class="page-actions">
                <button type="submit" class="btn btn--primary" id="btnGuardarPersonalizacion">
                    Guardar personalización
                </button>
            </div>
        </div>

        <div class="form-layout form-layout--personalizacion">
            <article class="panel">
                <div class="panel__header">
                    <div>
                        <h3>Datos del estudio</h3>
                        <p>Datos temporales para el encabezado y pie de página del recibo.</p>
                    </div>
                    <span class="badge badge--info">Demo temporal</span>
                </div>

                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <label>Logo</label>

                        <div class="logo-uploader">
                            <div class="logo-preview-box" id="personalizacionLogoPreviewBox">
                                <img src="" alt="Logo seleccionado" id="personalizacionLogoPreview" class="logo-preview-img">
                                <span id="personalizacionLogoTexto">ECC</span>
                            </div>

                            <div>
                                <input type="file" class="form-control" id="personalizacionLogo" accept="image/*">
                                <p class="input-help">
                                    Para el demo se previsualiza con FileReader. Más adelante se podrá guardar en la carpeta almacen.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre comercial</label>
                        <input type="text" class="form-control" id="personalizacionNombreComercial" required>
                    </div>

                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" class="form-control" id="personalizacionRuc" required>
                    </div>

                    <div class="form-group">
                        <label>Razón social</label>
                        <input type="text" class="form-control" id="personalizacionRazonSocial" required>
                    </div>

                    <div class="form-group">
                        <label>Rubro de la empresa</label>
                        <input type="text" class="form-control" id="personalizacionRubro" required>
                    </div>

                    <div class="form-group form-group--full">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="personalizacionDireccion" required>
                    </div>

                    <div class="form-group">
                        <label>Color principal</label>
                        <input type="color" class="form-control form-control--color" id="personalizacionColorPrincipal">
                    </div>

                    <div class="form-group">
                        <label>Color secundario</label>
                        <input type="color" class="form-control form-control--color" id="personalizacionColorSecundario">
                    </div>

                    <div class="form-group form-group--full">
                        <label>Texto superior</label>
                        <textarea class="form-control" id="personalizacionTextoSuperior"></textarea>
                    </div>

                    <div class="form-group form-group--full">
                        <label>Pie de página</label>
                        <textarea class="form-control" id="personalizacionPiePagina"></textarea>
                    </div>
                </div>
            </article>

            <article class="receipt-preview-mini receipt-preview-mini--live">
                <div class="receipt-preview-mini__header" id="previewMiniHeaderPersonalizacion">
                    <div class="receipt-logo-demo" id="previewMiniLogoPersonalizacion">
                        <img src="" alt="Logo del estudio" id="previewMiniLogoImgPersonalizacion" class="receipt-logo-demo__img">
                        <span id="previewMiniLogoTextoPersonalizacion">ECC</span>
                    </div>

                    <div>
                        <h3 id="previewMiniNombreComercial">Estudio Contable Contreras</h3>
                        <p id="previewMiniTextoSuperior">Generador de recibos personalizable</p>
                    </div>
                </div>

                <div class="receipt-preview-mini__body">
                    <div>
                        <span>RUC</span>
                        <strong id="previewMiniRuc">10730652441</strong>
                    </div>

                    <div>
                        <span>Razón social</span>
                        <strong id="previewMiniRazonSocial">MIRTHA VETTY BACA CONTRERAS</strong>
                    </div>

                    <div>
                        <span>Rubro</span>
                        <strong id="previewMiniRubro">Asesoría contable y tributaria</strong>
                    </div>

                    <div>
                        <span>Dirección</span>
                        <strong id="previewMiniDireccion">CALLE MARTINEZ DE CAMPAÑON 911</strong>
                    </div>

                    <div>
                        <span>Pie de página</span>
                        <strong id="previewMiniPiePagina">Gracias por su preferencia.</strong>
                    </div>
                </div>
            </article>
        </div>
    </form>
</section>