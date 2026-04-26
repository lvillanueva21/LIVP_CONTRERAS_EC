<section class="section-page" id="section-datos-base">
    <div class="page-title">
        <div>
            <span class="eyebrow">Catálogos temporales</span>
            <h2>Datos base</h2>
            <p>
                Aquí se administrarán clientes, servicios, bancos y cuentas de ahorro temporales.
            </p>
        </div>

        <button type="button" class="btn btn--primary" data-open-modal="modalDemo">
            Agregar dato demo
        </button>
    </div>

    <div class="tabs-demo">
        <button type="button" class="tab-demo activo">Clientes</button>
        <button type="button" class="tab-demo">Servicios</button>
        <button type="button" class="tab-demo">Bancos</button>
        <button type="button" class="tab-demo">Cuentas de ahorro</button>
    </div>

    <div class="panel">
        <div class="panel__header">
            <div>
                <h3>Datos temporales iniciales</h3>
                <p>Esta tabla es solo una vista previa visual. El CRUD se implementará en la siguiente fase.</p>
            </div>
            <span class="badge badge--info">Sin MySQL</span>
        </div>

        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Nombre / Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaDatosBase">
                    <tr>
                        <td colspan="4">Cargando datos temporales...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>