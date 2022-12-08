function initPopup(root, mapPopup){
    $(root).find('.popup_elmt').each(function (index){
        var elmt = $(this);
        elmt
        .find('input[name="check_popup"]')
        .change(function (e){
            Object.keys(mapPopup)
            .forEach((key) => {
                const val = elmt.find('.'+mapPopup[key]).attr('data-popup');
                const keySplit = key.split("___");
                const openerElmt = window.opener.document.getElementById(keySplit[0]);
                if(keySplit.length > 1){
                    openerElmt.dataset.myTarget = keySplit[1];
                }
                
                openerElmt.value = val;
                $( openerElmt ).trigger( "change" );
            });
            window.close();
        });
    });
}

