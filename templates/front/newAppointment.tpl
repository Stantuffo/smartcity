<nav class="navbar navbar-light bg-light text-center">
    <a class="navbar-brand" href="#">
        <img src="img/logo.png" width="30" height="30" class="d-inline-block align-top"
             alt="">
        SmartCity
    </a>
    <a href="/smartcity" class=" pt-2 btn btn-outline-theme btn-rounded">
        <h5 class="mb-1">Torna indietro
            <i class="far fa-arrow-alt-circle-left"></i>
        </h5>
    </a>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-12 text-center">
            <h2>Aggiungi Impegno:</h2>
        </div>
        <div class="col-12">
            <form action="insertAppointment" method="POST">
                <div class="form-group">
                    <label for="title">Oggetto</label><span class="text-red">*</span>
                    <input type="text" class="form-control" id="title" name="title"
                           placeholder="Inserisci oggetto dell'appuntamento" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-8">
                        <label for="address">Indirizzo</label>
                        <input type="text" class="form-control" id="address" name="address"
                               placeholder="Inserisci l'indirizzo della destinazione">
                    </div>
                    <div class="form-group col-4">
                        <label for="ciry">Città</label>
                        <input type="text" class="form-control" id="city" name="city"
                               placeholder="Inserisci la città della destinazione">
                    </div>
                </div>
                <div class="form-group">
                    <label for="latitutde">Latitudine</label><span class="text-red">*</span>
                    <input type="number" class="form-control" id="latitude" name="latitude"
                           placeholder="Inserisci la latitudine della destinazione" step=".0000001" required>
                    <small id="latHelp" class="form-text text-muted">La latitudine della destinazione deve essere
                        inserita in notazione decimale(Es.: 42.1234567). Sono ammesse fino a 7 cifre decimali.
                    </small>
                </div>
                <div class="form-group">
                    <label for="longitude">Longitudine</label><span class="text-red">*</span>
                    <input type="number" class="form-control" id="longitude" name="longitude"
                           placeholder="Inserisci la longitudine della destinazione" step=".0000001" required>
                    <small id="longHelp" class="form-text text-muted">La longitudine della destinazione deve essere
                        inserita in notazione decimale(Es.: 14.1234567). Sono ammesse fino a 7 cifre decimali.
                    </small>
                </div>
                <span class="text-red">* = Campo Obbligatorio</span>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-outline-theme">Aggiungi</button>
                </div>
            </form>
        </div>
    </div>
</div>