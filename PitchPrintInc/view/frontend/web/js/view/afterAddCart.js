(function($){
    $(document).on('ajax:addToCart', resetProd);

    const productSel = document.querySelector('#product_addtocart_form input[name=product]');
    const productId = productSel ? productSel.value : 0;
    
    function resetProd() {
        if (!productId 
            || !localStorage.hasOwnProperty('pp_w2p_projects')) 
            return;
                
        let currentLocalStorage = JSON.parse(localStorage.pp_w2p_projects);
        if(currentLocalStorage[productId]) delete currentLocalStorage[productId];
        else return;
        localStorage.pp_w2p_projects = JSON.stringify(currentLocalStorage);
    }
})(jQuery);