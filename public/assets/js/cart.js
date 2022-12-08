function formatPrix(prix){
    let text = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(prix);
    return text;
}

function getBasketItems(){
    let basketItemsDOM = document.getElementsByClassName('basket-item');
    let basketItems = [];
    for(let i=0; i<basketItemsDOM.length; i++){
        let basketItemDOM = basketItemsDOM[i];
        let productId = basketItemDOM.dataset.productId;
        let quantityInput = document.getElementById(`input-quantity-of-product-${productId}`);
        let quantity = quantityInput.value;
        basketItems.push({productId : productId, quantity : quantity});
    }
    return basketItems;
}
function updateBasketItems(){
    let basketItems = getBasketItems();
    let request = $.ajax({  
        url:        '/cart/update',  
        type:       'PUT', 
        data : JSON.stringify(basketItems),  
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
        console.log(errMessage);
        alert(errMessage);
    });
    
}
function updateBasketItem(productId){
    let quantityCounter = document.getElementById(`input-quantity-of-product-${productId}`);
    let quantity = parseInt(quantityCounter.value);
    console.log(productId);
    let request = $.ajax({  
        url:        '/cart/updateOne',  
        type:       'PUT', 
        data : JSON.stringify({productId:productId, quantity:quantity}),  
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
        console.log(errMessage);
        alert(errMessage);
    });
    
}


function removeBasketItem(productId){
    console.log(productId);
    let request = $.ajax({  
        url:        '/cart/remove/'+productId,  
        type:       'DELETE', 
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
        console.log(errMessage);
    });
    
}

function getTotalCostOf(productId){
    let basketItem = document.getElementById(`basket-item-${productId}`);
    let quantityCounter = document.getElementById(`input-quantity-of-product-${productId}`);
    let quantity = parseInt(quantityCounter.value);
    let unitPrice = parseFloat(basketItem.dataset.unitPrice);
    console.log(quantity);
    console.log(unitPrice);

    if(isNaN(quantity)) {
        quantityCounter.value = 0;
        quantity = 0;
    }
    let total = unitPrice * quantity;
    return total;
}
function refreshCartTotal(){
    let basketItems = getBasketItems();
    let total = 0;
    for(let i=0; i<basketItems.length; i++){
        let subtotal = getTotalCostOf(basketItems[i]['productId']);
        total += subtotal;
    }
    let subtotalDOM = document.getElementById(`cart-subtotal`);
    let totalDOM = document.getElementById(`cart-total`);
    subtotalDOM.innerHTML = formatPrix(total);
    totalDOM.innerHTML = formatPrix(total);

}
function refreshTotalCostOf(productId){
    
    let totalCost = document.getElementById(`total-cost-of-product-${productId}`);
    let total = getTotalCostOf(productId);
    totalCost.innerHTML = formatPrix(total);
    refreshCartTotal();
}