(function ($) {
    // Get Product Id
    let productSel = document.querySelector('#product_addtocart_form input[name=_selected_product_id]');
    let productId = productSel ? productSel.value : 0;
    let ppValEl = document.querySelector('#_pitchprint');
    
    function updatePPDataInCartForm () {
      if (!productId) {
         return;
      }
      if(!ppValEl) {
        return;
      }
      if (!localStorage.hasOwnProperty('pp_w2p_projects')) {
        return;
      }
      if (!JSON.parse(localStorage.pp_w2p_projects)[productId]) {
        return;
      }
      ppValEl.value = encodeURIComponent(JSON.stringify(JSON.parse(localStorage.pp_w2p_projects)[productId]));
      $('.gallery-placeholder').on('f:load',updatePPPreview);
    }
    
    function updatePPPreview(projectId = null) {
      
      let getProjectId = projectId ? projectId : JSON.parse(localStorage.pp_w2p_projects)[productId].projectId;
      setTimeout( _ =>{
        const _n = Math.random();
        const _prev = `https://s3-eu-west-1.amazonaws.com/pitchprint.io/previews/${getProjectId}_1.jpg?
                     rand=${_n}`;
        $('.gallery-placeholder').html(`<img src="${_prev}">`);
        $('.gallery-placeholder').css('text-align', 'center');
        $('.gallery-placeholder').css('width', '100%');
        activateResetButton();
      }, 500);
    }
    
    let resetAttemps = 50;
    function activateResetButton() {
      if (!resetAttemps) {
        return;
      }
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

    updatePPDataInCartForm();
})(jQuery);