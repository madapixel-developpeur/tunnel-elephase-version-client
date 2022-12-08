function openPopup(path, opener, popup, queryString = ''){
    var options = "resizable=yes,scrollbars=yes,location=no,width=1000,height=500,top=0,left=0";
    window.open(path+'?'+queryString+"&opener="+opener+"&popup="+popup, 'popUpWindow', options).focus();
}   