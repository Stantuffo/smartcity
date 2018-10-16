<nav class="navbar navbar-light bg-light text-center">
    <a class="navbar-brand" href="#">
        <img src="img/logo.png" width="30" height="30" class="d-inline-block align-top"
             alt="">
        SmartCity
    </a>
    <a href="addAppointment" class=" pt-2 btn btn-outline-theme btn-rounded">
        <h5 class="mb-1">Nuovo Impegno
            <i class="far fa-calendar-plus"></i>
        </h5>
    </a>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-12 text-center">
            <h2>Energy Status:</h2>
        </div>
        <div class="col-12 mlauto">
            <div class="c100 p{$batteryLevel} big {if $batteryLevel < 15}red{elseif $batteryLevel < 40}orange{elseif $batteryLevel < 75}green{else}{/if}">
                <span>{$batteryLevel}%</span>
                <div class="slice">
                    <div class="bar"></div>
                    <div class="fill"></div>
                </div>
            </div>
        </div>
        <div class="col-12 text-center">
            <h3>
                {$remainingKms} Kms remaining
            </h3>
        </div>
        <!--lista impegni-->
        <div class="col-12 mb-2">
            {foreach $impegni as $impegno}
                <div class="card">
                    <h5 class="card-header">
                        <div class="row">
                            <div class="col-9">
                                <strong>{$impegno.title}</strong> - {$impegno.address}
                                <h6 class="ml-3">Distance: <span
                                            class="{if $impegno.datiNavigazione.distanceValue <= $remainingKms * 1000}text-success{else}text-danger{/if}">{$impegno.datiNavigazione.distanceText}</span>
                                </h6>
                            </div>
                            <div class="col-3 d-flex justify-content-end">
                                <a href="https://www.google.com/maps/dir/?api=1&destination={$impegno.lat},{$impegno.lon}">
                                    <img src="img/navigation.png" class="img-little" alt="go!">
                                </a>
                            </div>
                        </div>
                    </h5>
                </div>
            {/foreach}
        </div>
    </div>
</div>