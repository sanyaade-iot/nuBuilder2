// nuObjectPanel

// requires jquery
// requires jquery-ui
// requires jquery-window-5.03

function getQueryParams(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]
            = decodeURIComponent(tokens[2]);
    }

    return params;
}

var $_GET = getQueryParams(document.location.search);

// Set the form title to have an alt click event that will launch the nuObjectPanel
$(document).ready(function(){
    $('#pagetitle').find('[ondblclick^=openForm]').click(function(event) {
        if (event.ctrlKey) {
            nuObjectPanelOpen('');
        }
    });
    // this is because nubuilder has no class for titles...
    $('div[id$=_title][ondblclick^=openForm]').click(function(event){
        if (event.ctrlKey) {
            nuObjectPanelOpen($(this).attr('id').substr(0,$(this).attr('id').length-6));
        }
    });
});

// function that opens the nuObjectPanel
function nuObjectPanelOpen(objectName){
    $.window({
       title: "nuObjectPanel",
       url: "nuobjectpanel.php?f="+$_GET.f+"&dir="+$_GET.dir+"&objname="+objectName,
       width: 500,
       height: 600,
       bookmarkable: false
    });
    
}