import PerfectScrollbar from 'perfect-scrollbar';

export default {
  init() {
   
    setInterval(function() {
      if ($('.modal-body:not(.ps)').length) {
        new PerfectScrollbar('.modal-body:not(.ps)');
      }
      if ($('.main-menu-area:not(.ps)').length) {
        new PerfectScrollbar('.main-menu-area:not(.ps)');
      }
    }, 300);
  }
}