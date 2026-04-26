<section class="section-page" id="section-auditoria">
    <div class="page-title">
        <div>
            <span class="eyebrow">Registro temporal</span>
            <h2>Auditoría</h2>
            <p>
                Aquí se mostrarán las acciones temporales realizadas durante la sesión demo.
            </p>
        </div>

        <button type="button" class="btn btn--light" id="btnAgregarAuditoriaDemo">
            Registrar acción demo
        </button>
    </div>

    <div class="panel">
        <div class="panel__header">
            <div>
                <h3>Acciones recientes</h3>
                <p>Estos registros no se guardan en base de datos durante esta fase.</p>
            </div>
            <span class="badge badge--info">Temporal</span>
        </div>

        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Tipo de acción</th>
                        <th>Descripción</th>
                        <th>Usuario demo</th>
                    </tr>
                </thead>
                <tbody id="tablaAuditoria">
                    <tr>
                        <td colspan="4">Todavía no hay acciones registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>