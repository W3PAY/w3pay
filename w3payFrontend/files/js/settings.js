document.addEventListener("DOMContentLoaded", function() {

    function getDivFormData(divFormElement) {
        var formData = new FormData();
        var inputTypeArr = ['input', 'select'];
        for (TypeName of inputTypeArr) {
                for (inputElement of divFormElement.querySelectorAll(TypeName)) {
                    if(inputElement.getAttribute('name')){
                        if(TypeName=='input' && (inputElement.getAttribute('type')=='checkbox' || inputElement.getAttribute('type')=='radio')){
                            if(inputElement.checked){
                                formData.append(inputElement.getAttribute('name'), inputElement.value);
                            }
                        } else {
                            formData.append(inputElement.getAttribute('name'), inputElement.value);
                        }
                    }
                }
        }
        return formData;
    }

    function SignOutForm(sendurl){
        //$this.querySelector(".formMessage").innerHTML = "";
        var formData = new FormData();
        formData.append('loadPage', 'SignOut');
        var xhr = new XMLHttpRequest();
        var urlCurrent = window.location.href;
        xhr.open("POST", sendurl, true);
        xhr.onreadystatechange = function() {//Вызывает функцию при смене состояния.
            if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                var res = JSON.parse(xhr.response);
                if(res.error==1){
                    console.log(res);
                }
                if(res.error==0){
                    location.reload();
                }
            }
        }
        xhr.send(formData);
    }

    function settingsW3payForm($this, sendurl){
        $this.querySelector('.FormBtn').classList.add('loadingType');
        $this.querySelector(".formMessage").innerHTML = "";
        //var formData = new FormData($this);
        var formData = getDivFormData($this);
        var xhr = new XMLHttpRequest();
        var urlCurrent = window.location.href;
        xhr.open("POST", sendurl, true);
        xhr.onreadystatechange = function() {//Вызывает функцию при смене состояния.
            $this.querySelector('.FormBtn').classList.remove('loadingType');
            if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                var res = JSON.parse(xhr.response);
                console.log(res);

                if(res.error==1){
                    $this.querySelector(".formMessage").innerHTML = "<div>"+res.data+"</div>";
                }
                if(res.error==0){
                    $this.querySelector(".formMessage").innerHTML = "<div>"+res.data+"</div>";
                }
            }
        }
        xhr.send(formData);
    }

    function savePasswordSettingsForm($this, sendurl){
        $this.querySelector(".formMessage").innerHTML = "";
        //var formData = new FormData($this);
        var formData = getDivFormData($this);
        var xhr = new XMLHttpRequest();
        var urlCurrent = window.location.href;
        xhr.open("POST", sendurl, true);
        xhr.onreadystatechange = function() {//Вызывает функцию при смене состояния.
            if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                var res = JSON.parse(xhr.response);
                console.log(res);

                if(res.error==1){
                    $this.querySelector(".formMessage").innerHTML = "<div>"+res.data+"</div>";
                }
                if(res.error==0){
                    $this.querySelector(".formMessage").innerHTML = "<div>"+res.data+"</div>";
                    //$this.submit();
                }
            }
        }
        xhr.send(formData);
    }

    function settingsW3payAuthForm($this, sendurl){
        $this.querySelector(".formMessage").innerHTML = "";
        //var formData = new FormData($this);
        var formData = getDivFormData($this);
        var xhr = new XMLHttpRequest();
        var urlCurrent = window.location.href;
        xhr.open("POST", sendurl, true);
        xhr.onreadystatechange = function() {//Вызывает функцию при смене состояния.
            if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                var res = JSON.parse(xhr.response);

                if(res.error==1){
                    $this.querySelector(".formMessage").innerHTML = "<div>"+res.data+"</div>";
                }
                if(res.error==0){
                    $this.closest('.loadPageContent').innerHTML = res.html;
                    clickUseWeb();
                    clickEnableFiatMulticurrency();
                    //$this.submit();
                }
            }
        }
        xhr.send(formData);
    }

    function clickUseWeb($this){
        if(document.querySelector('.useWeb3Input')){
            document.querySelector('.useWeb3Text').style.display = "none";
            document.querySelector('.ScanApiTokensBlock').style.display = "none";

            if(document.querySelector('.useWeb3Input').checked){
                document.querySelector('.useWeb3Text').style.display = "block";
            } else {
                document.querySelector('.ScanApiTokensBlock').style.display = "block";
            }
        }
    }
    clickUseWeb();

    function clickEnableFiatMulticurrency($this){
        if(document.querySelector('.enableFiatMulticurrencyInput')){
            document.querySelector('.backendCurrencyConversionApiBlock').style.display = "none";

            if(document.querySelector('.enableFiatMulticurrencyInput').checked){
                document.querySelector('.backendCurrencyConversionApiBlock').style.display = "block";
            } else {
                //document.querySelector('.backendCurrencyConversionApiBlock').style.display = "block";
            }
        }
    }
    clickEnableFiatMulticurrency();

    // click Event
    document.addEventListener('submit', function OnClick(event) {
        var $this = event.target;
        var ActionNameClass = '';

        /*ActionNameClass = 'settingsW3payForm';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            settingsW3payForm($this);
            return false;
        }

        ActionNameClass = 'savePasswordSettingsForm';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            savePasswordSettingsForm($this);
            return false;
        }

        ActionNameClass = 'deleteAllSettingsForm';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            if('remove all settings' == prompt("Are you sure you want to delete all settings files including the secret key and password from the settings file? To delete, enter \"remove all settings\".")){
                settingsW3payForm($this);
            }
            return false;
        }*/

    });

    // click Event
    document.addEventListener('click', function OnClick(event) {
        var $this = event.target;
        var ActionNameClass = '';
        var FormNameClass = '';

        ActionNameClass = 'useWeb3Input';
        if ($this.classList.contains(ActionNameClass)) {
            //event.preventDefault();
            clickUseWeb($this);
            return false;
        }

        ActionNameClass = 'enableFiatMulticurrencyInput';
        if ($this.classList.contains(ActionNameClass)) {
            //event.preventDefault();
            clickEnableFiatMulticurrency($this);
            return false;
        }

        ActionNameClass = 'savePasswordSettingsFormBtn';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            FormNameClass = 'savePasswordSettingsForm';
            savePasswordSettingsForm($this.closest('.'+FormNameClass), $this.closest('.'+FormNameClass).getAttribute('data-sendurl'));
            return false;
        }

        ActionNameClass = 'settingsW3payAuthFormBtn';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            FormNameClass = 'settingsW3payAuthForm';
            settingsW3payAuthForm($this.closest('.'+FormNameClass), $this.closest('.'+FormNameClass).getAttribute('data-sendurl'));
            return false;
        }

        ActionNameClass = 'settingsW3payFormBtn';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            FormNameClass = 'settingsW3payForm';
            settingsW3payForm($this.closest('.'+FormNameClass), $this.closest('.'+FormNameClass).getAttribute('data-sendurl'));
            return false;
        }

        ActionNameClass = 'deleteAllSettingsFormBtn';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            if('remove all settings' == prompt("Are you sure you want to delete all settings files including the secret key and password from the settings file? To delete, enter \"remove all settings\".")){
                FormNameClass = 'deleteAllSettingsForm';
                settingsW3payForm($this.closest('.'+FormNameClass), $this.closest('.'+FormNameClass).getAttribute('data-sendurl'));
            }
            return false;
        }

        ActionNameClass = 'signoutBtn';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            SignOutForm($this.getAttribute('data-sendurl'));
            return false;
        }

    });

});