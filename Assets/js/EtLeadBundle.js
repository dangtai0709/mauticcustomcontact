MauticVars.liveCache = new Array();
MauticVars.lastSearchStr = "";
MauticVars.globalLivecache = new Array();
MauticVars.lastGlobalSearchStr = "";

Mautic.leadOnLoad = function(container, response) {
  mQuery("#list-status li a").on("click", function() {
    let overlayEnabled,
      liveCacheVar,
      overlayTarget = ".page-list",
      target = ".page-list";
    // Active tag clicked
    mQuery("#list-status li a.active").removeClass("active");
    mQuery(this).addClass("active");

    // Get value from tag clicked
    const value = mQuery(this).data("status");
    const route = mQuery(this).data("route");
    if (typeof liveCacheVar == "undefined") {
      liveCacheVar = "liveCache";
    }
    const searchName = "search";
    if (typeof Mautic.liveSearchXhr !== "undefined") {
      //ensure current search request is aborted
      Mautic["liveSearchXhr"].abort();
    }
    const tmpl = "list";
    var tmplParam = route.indexOf("tmpl") == -1 ? "&tmpl=" + tmpl : "";
    // In a modal?
    var checkInModalTarget = overlayTarget ? overlayTarget : target;
    var modalParent = mQuery(checkInModalTarget).closest(".modal");
    var inModal = mQuery(modalParent).length > 0;

    if (inModal) {
      var modalTarget = "#" + mQuery(modalParent).attr("id");
      Mautic.startModalLoadingBar(modalTarget);
    }
    var showLoading = inModal ? false : true;

    Mautic.liveSearchXhr = mQuery.ajax({
      showLoadingBar: showLoading,
      url: route,
      type: "GET",
      data: searchName + "=" + encodeURIComponent(value) + tmplParam,
      dataType: "json",
      success: function(response) {
        mQuery("#list-search[name=search]").val(value);
        //cache the response
        if (response.newContent) {
          MauticVars[liveCacheVar][value] = response.newContent;
        }
        //note the target to be updated
        response.target = target;
        response.overlayEnabled = overlayTarget;
        response.overlayTarget = overlayTarget;

        if (inModal) {
          Mautic.processModalContent(response);
          Mautic.stopModalLoadingBar(modalTarget);
        } else {
          Mautic.processPageContent(response);
          Mautic.stopPageLoadingBar();
        }
      },
      error: function(request, textStatus, errorThrown) {
        Mautic.processAjaxError(request, textStatus, errorThrown);
      },
      complete: function() {
        delete Mautic.liveSearchXhr;
        delete Mautic.filterButtonClicked;
      }
    });
  });
};
