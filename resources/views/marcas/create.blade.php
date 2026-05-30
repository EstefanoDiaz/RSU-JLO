<div class="modal fade" id="modalMarca">

    <div class="modal-dialog">

        <form
            action="{{ route('marcas.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="modal-content">

                <div class="modal-header bg-primary">

                    <h5>
                        Nueva Marca
                    </h5>

                </div>

                <div class="modal-body">

                    <div class="form-group">

                        <label>Nombre</label>

                        <input
                            type="text"
                            name="nombre"
                            class="form-control">

                    </div>

                    <div class="form-group">

                        <label>Descripción</label>

                        <textarea
                            name="descripcion"
                            class="form-control"></textarea>

                    </div>

                    <div class="form-group">

                        <label>Logo</label>

                        <input
                            type="file"
                            name="imagen"
                            class="form-control">

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-danger"
                        data-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        Guardar

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>