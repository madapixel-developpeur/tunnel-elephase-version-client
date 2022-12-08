const listeLangues = [
    'fr',
    'en'
]

function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'se',   includedLanguages: listeLangues.join(',')}, 'google_translate_element');
}

function SelectTriggerGoogleTrad(e) {
  setLocalValue(e.target.value, true, function() {
      location.reload()
  });
}

function setLocalValue(local, saveInSession = true, callback=null) {
    $('select.goog-te-combo>option[value="'+local+'"]')[0].selected = 'selected'
    $('select.goog-te-combo')[0].dispatchEvent(new Event('change'))
    if(saveInSession) {
        // enregistrement en session du local
        $.ajax({
            url:'/setLocalSession',
            method : 'POST',
            data: { local: local },
            success: function() {
                if(callback) {
                    callback();
                }
            }
        })
    }
    // pour eviter d'avoir un margin-top
    document.body.style+=document.body.style+';top:0px!important'
}
const defaultLocal = document.getElementById('google_translate_element').getAttribute('data-local')
const interval = setInterval(function() {
    if($('select.goog-te-combo>option').length>0) {
        setLocalValue(defaultLocal ?? 'fr', false)
        clearInterval(interval)
    }
},200)

