<body id="LoginForm">
<div class="container">
    <div class="login-form">
        <div class="main-div">
            <h3 class="bordered-grey rounded pt-2 pb-2">
                <img src="/logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
                SmartCity
            </h3>
            <div class="panel">
                <h2>Login</h2>
                <p>Inserisci Email e Password per accedere</p>
            </div>
            <form action="login" method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" id="email" name="email" placeholder="Email Address">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <div class="forgot">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
        <span id="error"></span>
    </div>
</div>
</body>
