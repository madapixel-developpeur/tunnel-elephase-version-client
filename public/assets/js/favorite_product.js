
function toggleFavorite(productId){
    let request = $.ajax({  
        url:        '/product/favorite/toggle',  
        type:       'POST', 
        data : JSON.stringify({productId:productId}),  
        contentType: 'application/json; charset=utf-8',
        dataType: "json"
    });  
    request.done(function(data) {
        // your success code here
            console.log(data);
            location.reload();
    });

    request.fail(function(jqXHR, textStatus) {
        // your failure code here
        let errMessage = jqXHR.responseJSON.message;
        console.log(jqXHR);
        alert("Erreur: "+errMessage);
    });
    
}
