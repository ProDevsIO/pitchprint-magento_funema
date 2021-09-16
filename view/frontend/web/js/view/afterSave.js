(function($){
    window.ppclient.on('project-saved', storeToLocal);
   
    // Get Product Id
    const productSel = document.querySelector('#product_addtocart_form input[name=_selected_product_id]');
    const productId = productSel ? productSel.value : 0;
    const ppValEl = document.querySelector('#_pitchprint');

    // Save project ID against product ID in localStorage.
    async function storeToLocal(e) {
        if (!localStorage.hasOwnProperty('pp_w2p_projects')) 
            localStorage.pp_w2p_projects = JSON.stringify({});
        if (!productId) {
            console.log('Can not find product ID!');
            return;
        }
            
        let currentLocalStorage = JSON.parse(localStorage.pp_w2p_projects);
        let _projectData = await getPPInputValue();
        currentLocalStorage[productId] = JSON.parse(decodeURIComponent(_projectData));
        localStorage.pp_w2p_projects = JSON.stringify(currentLocalStorage);
        
        updatePPPreview();
    }
    
    let attempts = 20;
    let _interval;
    
    function getValueNow(_resolve) {
        if (ppValEl.value) {
            clearInterval(_interval)
            _resolve(ppValEl.value);
        }
    }
    
    function getPPInputValue() {
        return new Promise(_res=>{
            if(!ppValEl.value)
                _interval = setInterval(_=>getValueNow(_res),500);
            else _res(ppValEl.value);        
        });
    
    }
    
    function updatePPPreview() {
        $ = jQuery;
        setTimeout(_=>{
            const _n = Math.random();
            const _prev = `https://s3-eu-west-1.amazonaws.com/pitchprint.io/previews/${JSON.parse(localStorage.pp_w2p_projects)[productId].projectId}_1.jpg?
                         rand=${_n}`;
            $('.gallery-placeholder').html(`<img src="${_prev}">`);
            if(!window.ppclient.galleryCentered) centerGal();
            if(!window.ppclient.resetActive) activateResetButton();
        },500);
    }
    
    function centerGal() {
        $('.gallery-placeholder').css('text-align', 'center');
        window.ppclient.galleryCentered = 1;
    }
    
    let resetAttemps = 50;
    function activateResetButton() {
        if (!resetAttemps) return;
        if (!$('#pp_clear_design_btn').length) {
            setTimeout(activateResetButton,500);
            resetAttemps--;
            return;
        }
        $('#pp_clear_design_btn').click(clearPPProject);
        window.ppclient.resetActive = 1;
    }
    
    function clearPPProject() {
      if (!productId) {console.log('No Product ID found!');return;}
      let newLocalS = JSON.parse(localStorage.pp_w2p_projects)
      delete newLocalS[productId];
      localStorage.pp_w2p_projects = JSON.stringify((newLocalS));
      window.location.reload();
    }
})(jQuery);