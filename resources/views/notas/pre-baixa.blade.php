@extends('layout.app')

@section('titulo')
Baixa de Notas via Retorno Bancário
@endsection

@section('content')
 <form action="{{ url('notas/posbaixa') }}" method="POST" enctype="multipart/form-data" id="formUploadRetorno">
   @csrf
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Upload de arquivo(s) de retorno (.RET)</h4>
                </div>

                <div class="card-body">
                    <p>
                        Envie um ou mais arquivos de retorno CNAB 400 do banco (extensão .RET).
                        O sistema vai ler os títulos e mostrar uma tela de conferência antes de
                        gravar qualquer baixa — nada é alterado automaticamente.
                    </p>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="dropZone" class="dropzone-upload">
                                <i class="fa fa-cloud-upload dropzone-icon"></i>
                                <p class="dropzone-texto-principal">Arraste os arquivos .RET aqui</p>
                                <p class="dropzone-texto-secundario">ou clique para escolher no computador</p>
                            </div>
                            <input type="file" name="arquivos[]" id="arquivos" multiple accept=".ret,.RET,.txt" style="display:none">
                        </div>
                    </div>

                    <div class="row" id="listaArquivosWrapper" style="display:none">
                        <div class="col-md-12">
                            <hr>
                            <p><b>Arquivos selecionados:</b></p>
                            <ul class="list-group" id="listaArquivos"></ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info btn-round text-right" id="processar" disabled>Processar</button>
                </div>

            </div>
        </div>
    </div>

</div>
</form>
@endsection

@section('scripts')
<style>
.dropzone-upload {
    border: 2px dashed #66615B;
    border-radius: 10px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    background-color: #f7f7f7;
    transition: all 0.2s ease-in-out;
}
.dropzone-upload:hover,
.dropzone-upload.dropzone-ativa {
    border-color: #1DC7EA;
    background-color: #eefbfd;
}
.dropzone-icon {
    font-size: 42px;
    color: #66615B;
    margin-bottom: 10px;
    display: block;
}
.dropzone-texto-principal {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 2px;
}
.dropzone-texto-secundario {
    font-size: 13px;
    color: #9A9A9A;
    margin-bottom: 0;
}
#listaArquivos .list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<script type="text/javascript">
(function () {
    var dropZone = document.getElementById('dropZone');
    var input = document.getElementById('arquivos');
    var listaWrapper = document.getElementById('listaArquivosWrapper');
    var lista = document.getElementById('listaArquivos');
    var botaoProcessar = document.getElementById('processar');

    function formatarTamanho(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function removerArquivo(indice) {
        var dt = new DataTransfer();
        var arquivos = Array.from(input.files);
        arquivos.splice(indice, 1);
        arquivos.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
        atualizarLista();
    }

    function atualizarLista() {
        lista.innerHTML = '';
        var arquivos = Array.from(input.files);

        if (arquivos.length === 0) {
            listaWrapper.style.display = 'none';
            botaoProcessar.disabled = true;
            return;
        }

        arquivos.forEach(function (arquivo, indice) {
            var li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML =
                '<span><i class="fa fa-file-text-o"></i> ' + arquivo.name +
                ' <small class="text-muted">(' + formatarTamanho(arquivo.size) + ')</small></span>' +
                '<button type="button" class="btn btn-danger btn-sm btn-remover-arquivo" data-indice="' + indice + '">' +
                '<i class="fa fa-times"></i></button>';
            lista.appendChild(li);
        });

        lista.querySelectorAll('.btn-remover-arquivo').forEach(function (btn) {
            btn.addEventListener('click', function () {
                removerArquivo(parseInt(this.getAttribute('data-indice'), 10));
            });
        });

        listaWrapper.style.display = 'block';
        botaoProcessar.disabled = false;
    }

    dropZone.addEventListener('click', function () {
        input.click();
    });

    input.addEventListener('change', atualizarLista);

    ['dragenter', 'dragover'].forEach(function (evento) {
        dropZone.addEventListener(evento, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dropzone-ativa');
        });
    });

    ['dragleave', 'drop'].forEach(function (evento) {
        dropZone.addEventListener(evento, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dropzone-ativa');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        var dt = new DataTransfer();
        Array.from(input.files).forEach(function (f) { dt.items.add(f); });
        Array.from(e.dataTransfer.files).forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
        atualizarLista();
    });
})();
</script>
@endsection
