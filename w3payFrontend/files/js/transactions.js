document.addEventListener("DOMContentLoaded", function() {

    function isJsonString(str) {
        try { JSON.parse(str); } catch (e) { return false; }
        return true;
    }

    function loadTxPage($this){
        var pagelog = $this.getAttribute('data-page');
        var sendurl = $this.getAttribute('data-sendurl');
        var checkPaymentPageUrl = $this.getAttribute('data-checkPaymentPageUrl');

        var formData = new FormData();
        formData.append('pagelog', pagelog);
        formData.append('checkPaymentPageUrl', checkPaymentPageUrl);
        formData.append('loadPage', 'TransactionsPage');

        var xhr = new XMLHttpRequest();
        xhr.open("POST", sendurl, true);
        xhr.onreadystatechange = function() {//Вызывает функцию при смене состояния.
            if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                if(isJsonString(xhr.response)){
                    var res = JSON.parse(xhr.response);

                    if(res.error==1){
                        $this.closest('.transactionsW3payBlock').querySelector(".txBlock").innerHTML = "<div>"+res.data+"</div>";
                    }
                    if(res.error==0){
                        $this.closest('.transactionsW3payBlock').querySelector(".txBlock").innerHTML = res.html;
                    }
                } else {
                    $this.closest('.transactionsW3payBlock').querySelector(".txBlock").innerHTML = xhr.response;
                }
            }
        }
        xhr.send(formData);
    }

    if(document.querySelector('.loadTxPage')){
        loadTxPage(document.querySelector('.loadTxPage'));
    }

    // click Event
    document.addEventListener('click', function OnClick(event) {
        var $this = event.target;
        var ActionNameClass = '';
        var FormNameClass = '';

        ActionNameClass = 'loadTxPage';
        if ($this.classList.contains(ActionNameClass)) {
            event.preventDefault();
            loadTxPage($this);
            return false;
        }

    });

});