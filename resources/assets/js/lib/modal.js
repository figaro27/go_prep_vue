import PerfectScrollbar from "perfect-scrollbar";

let menuPs = null;

export default {
  init() {
    setInterval(function() {
      const isMobile = $("#xs:visible").length;

      if ($(".modal-body:not(.ps)").length) {
        new PerfectScrollbar(".modal-body:not(.ps)");
      }
    }, 300);
  }
};
