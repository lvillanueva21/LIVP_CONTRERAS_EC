<section class="section-page activo" id="section-inicio">
    <div class="page-title">
        <div>
            <span class="eyebrow">Panel principal</span>
            <h2>Inicio</h2>
            <p>
                Resumen temporal del generador de recibos del Estudio Contable Contreras.
            </p>
        </div>

        <button type="button" class="btn btn--primary" data-section-go="generar-recibo">
            Crear recibo temporal
        </button>
    </div>

    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-card__icon">👥</div>
            <div>
                <p>Clientes temporales</p>
                <h3 id="statClientes">0</h3>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__icon">🧮</div>
            <div>
                <p>Servicios temporales</p>
                <h3 id="statServicios">0</h3>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__icon">🎨</div>
            <div>
                <p>Plantillas demo</p>
                <h3 id="statPlantillas">0</h3>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__icon">🧾</div>
            <div>
                <p>Recibos generados</p>
                <h3 id="statRecibos">0</h3>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__icon">💰</div>
            <div>
                <p>Total acumulado en soles</p>
                <h3 id="statTotalAcumulado">S/ 0.00</h3>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__icon">🕒</div>
            <div>
                <p>Última acción registrada</p>
                <h3 id="statUltimaAccion">Sin acciones</h3>
            </div>
        </article>
    </div>

    <div class="panel-grid">
        <article class="panel">
            <div class="panel__header">
                <div>
                    <h3>Estado del demo</h3>
                    <p>Primera implementación visual del sistema.</p>
                </div>
                <span class="badge badge--info">Fase 1</span>
            </div>

            <div class="timeline">
                <div class="timeline__item completado">
                    <span></span>
                    <div>
                        <h4>Base visual creada</h4>
                        <p>Sidebar, dashboard, navegación interna, cards, tablas, inputs, modales y notificaciones.</p>
                    </div>
                </div>

                <div class="timeline__item">
                    <span></span>
                    <div>
                        <h4>Datos base</h4>
                        <p>Clientes, servicios, bancos y cuentas de ahorro temporales.</p>
                    </div>
                </div>

                <div class="timeline__item">
                    <span></span>
                    <div>
                        <h4>Plantillas y personalización</h4>
                        <p>Logo, colores, campos visibles y orientación vertical u horizontal.</p>
                    </div>
                </div>

                <div class="timeline__item">
                    <span></span>
                    <div>
                        <h4>Generador de recibos</h4>
                        <p>Conceptos, periodos, totales, vista previa y exportación.</p>
                    </div>
                </div>
            </div>
        </article>

        <article class="panel">
            <div class="panel__header">
                <div>
                    <h3>Vista rápida</h3>
                    <p>Datos inventados cargados temporalmente.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Detalle</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaVistaRapida">
                        <tr>
                            <td colspan="3">Cargando datos temporales...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>