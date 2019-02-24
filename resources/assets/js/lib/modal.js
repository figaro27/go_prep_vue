import PerfectScrollbar from 'perfect-scrollbar';

let menuPs = null;

export default {
  init() {
   
    setInterval(function() {
      const isMobile = $('#xs:visible,#sm:visible').length;

      if ($('.modal-body:not(.ps)').length) {
        new PerfectScrollbar('.modal-body:not(.ps)');
      }
      if (!isMobile && $('.main-menu-area:not(.ps)').length) {
        menuPs = new PerfectScrollbar('.main-menu-area:not(.ps)');
      }
      else if(isMobile && menuPs) {
        menuPs.destroy();
        menuPs = null;
      }
    }, 300);
  }
}