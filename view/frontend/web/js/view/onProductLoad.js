(function ($) {
  // Get Product Id
  const productSel = document.querySelector('#product_addtocart_form input[name=_selected_product_id]');
  const productId = productSel ? productSel.value : 0;
  const ppValEl = document.querySelector('#_pitchprint');
  
  function updatePPDataInCartForm () {
    if (!productId) return;
    if(!ppValEl) return;
    if (!localStorage.hasOwnProperty('pp_w2p_projects'))
      return;
    if (!JSON.parse(localStorage.pp_w2p_projects)[productId])
      return;
    ppValEl.value = encodeURIComponent(JSON.stringify(JSON.parse(localStorage.pp_w2p_projects)[productId]));
    $('.gallery-placeholder').on('f:load',updatePPPreview);
  }
  
  function updatePPPreview() {
    setTimeout(_=>{
      const _n = Math.random();
      const _prev = `https://s3-eu-west-1.amazonaws.com/pitchprint.io/previews/${JSON.parse(localStorage.pp_w2p_projects)[productId].projectId}_1.jpg?
                   rand=${_n}`;
      $('.gallery-placeholder').html(`<img src="${_prev}">`);
      $('.gallery-placeholder').css('text-align', 'center');
      activateResetButton();
    },500);
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
  updatePPDataInCartForm();
})(jQuery);
