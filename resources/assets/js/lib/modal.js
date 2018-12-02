import PerfectScrollbar from 'perfect-scrollbar';

export default {
  init() {
   
    setInterval(function() {
      if ($('.modal-body:not(.ps)').length) {
        new PerfectScrollbar('.modal-body:not(.ps)');
      }
    }, 300);
  }
}