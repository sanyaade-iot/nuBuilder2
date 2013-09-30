/*
** File:           common.js
** Author:         nuSoftware
** Created:        2007/04/26
** Last modified:  2012/08/30
**
** Copyright 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012 nuSoftware
**
** This file is part of the nuBuilder source package and is licensed under the
** GPLv3. For support on developing in nuBuilder, please visit the nuBuilder
** wiki and forums. For details on contributing a patch for nuBuilder, please
** visit the `Project Contributions' forum.
**
**   Website:  http://www.nubuilder.com
**   Wiki:     http://wiki.nubuilder.com
**   Forums:   http://forums.nubuilder.com
*/

//-------------------------------DEBUG WINDOW-------------   
// Debugging console window variable
var nu_debugwin;

var nuShiftKey     = false;
var nuControlKey   = false;
var nuFirstClick   = false;
var offsetXIF      = 0;
var offsetYIF      = 0;
var shiftKeyIF     = false;
var mouseIsDownIF  = false;

function nuImage(pID){
//-- pID can be the zzsys_image_id or sim_code from zzsys_image
	return getImage(pID);
}

// Display and add text to a debugging console window
function nuDebug(text) {

	// Open the console window if it isn't currently
	if ((undefined == nu_debugwin) || nu_debugwin.closed) {
	
		// Open a new popup window
		nu_debugwin = window.open('about:blank', '_blank', 'location=no, menubar=no, status=no, toolbar=no, resizable=yes');
	
		// Set up the base HTML document
		nu_debugwin.document.write('<html><head><title>Debug Window</title></head><body style="font-family: monospace;"><h2>Debugging Output</h2><br/>\n');
	}
	
	// Write the debugging text to the page
	nu_debugwin.document.write(text + '<br/>\n');
}
   
//-------------------------------END DEBUG WINDOW-------------

//----function run  after no blanks or check duplicates , fails
function runBeforeCancel(offending, type){
	if(!offending){offending = "";}
  	if(!type){type = "";}
  var nuScreen = window.parent;

  if(nuScreen.nuAfterCheckDuplicates){;
      nuScreen.nuAfterCheckDuplicates(offending, type);
  }

}

/*
function useSameWindow(){

	if(nuSingleWindow()){
		alert('yes');
	}else{
		alert('no');
	}

}
*/

function nuRemoveElement(pID){  //-- removes an element from the DOM by passing its id

   var d      = document.getElementById(pID).parentNode;
   var olddiv = document.getElementById(pID);
   d.removeChild(olddiv);

}



function nuGetFormId(){  //--returns the Primary Key value for the Form
    return document.getElementById('recordID').value;

}

function nuGetRowId(){  //--returns the Primary Key value for the Subform row with focus

   var SFname     = String();
   var PKname     = String();
   var PKproperty = String();
   SFname         = nuGetRow();
   PKproperty     = 'primarykey' + SFname.substring(0,SFname.length-4); 
   PKname         = document.getElementById(PKproperty).value;
   if(document.getElementById(nuGetRow() + PKname)){
      SFname      = document.getElementById(nuGetRow() + PKname).value;
      return SFname;
   }else{
      return '';
   }

}

function nuGetRowObjectValue(pObjectName){  //--returns the Object (column) value for the Subform row with focus

   if(document.getElementById(nuGetRow() + pObjectName)){
      SFname      = document.getElementById(nuGetRow() + pObjectName).value;
      return SFname;
   }else{
      return '';
   }

}
   
   
function ajaxObject(){

   try{
       return new XMLHttpRequest();
   }
   catch (e){
       try{   
           return new ActiveXObject('Msxml2.XMLHTTP');
       }
       catch (e){
           try{
               return new ActiveXObject('Microsoft.XMLHTTP');
           }
           catch (e){
               alert('Your browser does not support AJAX!');
               return false;
           }
       }
   }


} 

   
//--creates an array of row prefixes of a subform
function nuSubformRowArray(pSubformName, pJustUnticked){

	var TheRows            = Number(document.getElementById('rows'+pSubformName).value);
	var TheArray           = Array();
	var ThePrefix          = String();
	var RowNo              = String();
	var JustUnticked       = false;
	if(pJustUnticked){
		if(document.getElementById('row' + pSubformName + '0000')){  //-- tick boxes exist
			JustUnticked   = true
		}
	}

	for(i = 0;i < TheRows; ++i){

		RowNo              = '0000' + String(i);
		RowNo              = RowNo.substring(RowNo.length - 4);
		ThePrefix          = pSubformName + RowNo;

		if(!JustUnticked || !document.getElementById('row' + ThePrefix).checked){
			TheArray[TheArray.length]    = ThePrefix;
		}

	}

	return TheArray;
}




function nuJax(pURL) {
//-- pass url to run

   var d                                                  = new Date();
   theID                                                  = 'a' + String(d.getTime()) + 'a';
   newObj                                                 = document.createElement('div');
   newObj.setAttribute('id', 'div_'+theID);
   newObj.innerHTML                                       = "<iframe src='' id='" + theID + "' />";
   document.body.appendChild(newObj);
   document.getElementById(theID).style.position          = 'absolute';
   document.getElementById(theID).style.height            = '0';
   document.getElementById(theID).style.width             = '0';
   document.getElementById(theID).style.backgroundColor   = 'red';
   document.getElementById(theID).style.overflow          = 'hidden';
   document.getElementById(theID).style.top               = '100';
   document.getElementById(theID).style.left              = '100';
   document.getElementById(theID).style.visibility        = 'visible';
   document.getElementById(theID).src                     = pURL;

}


function jsDateToSqlDate(pDate){
//-- parameter passed is a js date object
//-- returns a string formatted like "yyyy-mm-dd hh:mm:ss"
   var d        = pDate;
   var sqlDate  = String();

   sqlDate      =           d.getFullYear()           + '-';
   sqlDate      = sqlDate + twoChar(d.getMonth() + 1) + '-';
   sqlDate      = sqlDate + twoChar(d.getDate())      + ' ';
   sqlDate      = sqlDate + twoChar(d.getHours())     + ':';
   sqlDate      = sqlDate + twoChar(d.getMinutes())   + ':';
   sqlDate      = sqlDate + twoChar(d.getSeconds());

   return sqlDate;
}


function twoChar(pString){

   var vString = String(pString);

   if(vString.length == 1){
      vString   = '0' + vString + '';
   }
   return vString;
}





function nuColumnTotal(pthis, pTotalField){
	var SF           = String('');
	var SFname       = String('');
	var FDname       = String('');
	var ROWno        = String('');
	var TESTname     = String('');
	var theTotal     = Number(0);
	var SFlist       = Number(document.getElementById('theform').TheSubforms.value);
	for(i=0;i<SFlist;i++){
		if(SF==''){
			SFname              = document.getElementById('theform')['SubformNumber'+i].value;
			if(SFname == pthis.name.substr(0,SFname.length)){
				FDname      = pthis.name.substr(SFname.length+4);
				TESTname    = SFname + '0000' + FDname;
				if(TESTname.length == pthis.name.length){
					SF  = SFname;
				}
			}
		}
		
	}
	if(SF==''){
		theTotal                            = Number(document.getElementById('theform')[SFname+ROWno+FDname].value);
	}else{
		for(i=0;i<Number(document.getElementById('theform')['rows'+SFname].value);i++){
			ROWno                       = rowString(i);
			if(document.getElementById('theform')['deletebox'+SFname].value == '1'){
				if(!document.getElementById('theform')['row'+SFname+ROWno].checked){
					theTotal    = theTotal + Number(document.getElementById('theform')[SFname+ROWno+FDname].value);
				}
			}
		}
	}
	document.getElementById('theform')[pTotalField].value = theTotal;
	nuFormat(document.getElementById('theform')[pTotalField]);
}


function rowString(pRow){
	var zeros  = '0000';
	return zeros.substr(0,4-String(pRow).length)+String(pRow);
}


function nuMask(pthis, plist){
	var str = '';
	var len = 0;
	var gap = 0;
	var sep = '';
	var max = 0;

	for (var i = 0; i < plist.split(",").length; i++){
		max = max + Number(plist.split(",")[i]);
		i++;
		max = max + Number(plist.split(",")[i].length);
	}

	for (var i = 0; i < plist.split(",").length; i++){
		gap = Number(plist.split(",")[i]);
		i++;
		sep = plist.split(",")[i];
		str = str + pthis.value.substr(len,gap);
		if(pthis.value.substr(len,gap).length == gap){
			str = str + sep;
		}
		len = len + gap + plist.split(",")[i].length;
	}
	pthis.value = str.substr(0,max);

	return pthis.value.length != max;

}

function uDB(pThis,pType){ //---18-jan-2012 sc
	nuFormatField(pThis,pType);
}

function nuFormatField(pThis,pType){ //---18-jan-2012 sc

var id = pThis.id;
var lb = $(pThis);

   if(arguments.length == 1){
      pType = 'text';
   }
   if(document.getElementById('z___' + id)){            //--check if there is such a fieldname
      document.getElementById('z___' + id).value = ''; //--set value to blank so that it gets updated
   }

   if(pThis.accept!='' && pType == 'text'){;
   	   nuFormat(pThis);
   }
   if(arguments.length==1){ 
	   pType = '';
   }
   
   
   
   var vars = [];
   if(pType == 'listbox'){
	  
	  $('option:selected',lb).each(function(){
		  vars.push(this.value);
	  });
   }else if(pType == 'lookup'){
	  vars = [
			document.getElementById(                id).value
			,document.getElementById('code'        + id).value
			,document.getElementById('description' + id).value
	  ];
   }else{
      vars.push(lb.val());
   }

    var data = {
		name:  pThis.id
		,type: pType
		,dir: customDirectory()
		,id: form_session_id()
		,ses: session_id()
		,"var": vars
	}
   $.post('formsetvalues.php',data);
   
 document.theform.beenedited.value = '1';
// parent.document.theform.beenedited.value = '1';

}






function openCalendar(pTarget, pDay, pFormat){
	var url = 'calendar.php?target=' + pTarget + '&pDay=' + pDay + '&theFormat=' + pFormat+'&dir='+customDirectory();
	foc=window.open(url,'theCal'+safeCustomDirectory(),'width=300,height=300');
	foc.focus();

}


function openFileUpload(pFileName){

//--pFileName is the name of the text object on the opener document that will be updated with the new name of the file that has been uploaded

	var url = 'fileuploader.php?dir=' + customDirectory() + '&ses=' + session_id() + '&field=' + pFileName;
	foc=window.open(url,'theldr'+safeCustomDirectory(),'width=380,height=100');
	foc.focus();

}


function openImageUpload(){

	var url = 'imageuploader.php?dir=' + sd() + '&iid=' + document.getElementById('recordID').value;
	foc=window.open(url,'theldr'+safeCustomDirectory(),'width=380,height=100');
	foc.focus();

}


function openDownload(pFileName){

   w = window.open(web_root_path()+document.getElementById(pFileName).value,'test'+safeCustomDirectory(),maximumScreen());
   w.focus();
   
}



function backToIndex(){
	try{
		var current = top.parent;
		for(i = 0; i < 7; i++){
			if(current.name == 'index'){
				//alert('index found at a depth of: ' + i);
				current.focus();
				return;
			}
			current = current.opener;
		}
	}catch(error){
		return;
	}

}


function maximumScreen(){
	return "top=0,left=0,titlebar=no,resizable=yes,status=0,scrollbars=yes,titlebar=no";
}


function isFirstClick(){

   if(!nuSingleWindow()){ 
	  return true;
   }
   if(nuFirstClick){ 
      if(nuControlKey == false){ 
         nuFirstClick = false;
      }
	  return true;
   }else{
      return false;
   }
}


function openBrowse(pFormID, pFilter, pPrefix, pSession, pFormSession, pLookupCode, pPreSearch){

//---pSession = formsessionID if browsing a lookup or session_id if editing a record

	if(!pLookupCode){
		pLookupCode            = "";
	}
	while(pFilter.search("#") != -1){
		pFilter                = pFilter.replace("#", "%23");
	}
	var nuopener                   = document.getElementById('formsessionID').value;
	var uName                  = '_self';
	var vURL                   = 'browse.php?x=1&p=1&f=' + pFormID + '&s=' + pFilter + '&nuopener=' + nuopener + '&prefix=' + pPrefix + '&dir=' + customDirectory() + '&ses='+ pSession + '&form_ses='+ pFormSession + '&lookup_code='+ pLookupCode;
    if (typeof pPreSearch     != 'undefined'){
		vURL                   = vURL + '&presearch=' + pPreSearch;
	}
	if(nuControlKey || !nuSingleWindow()){  
		uName                  = 'B'  + pFormID + safeCustomDirectory();
	}else{
		vURL                   = vURL + addHistory();
	}
	nuControlKey               = false;
	e                          = window.open(vURL,uName,maximumScreen());
	e.focus();
	return true;

}

function openIBrowse(pFormID, pFilter, pPrefix, pSession, pFormSession){ //in an iFrame

	while(pFilter.search("#") != -1){
		pFilter                = pFilter.replace("#", "%23");
	}
	var vURL                   = 'browse.php?x=1&p=1&f=' + pFormID + '&s=' + pFilter + '&nuopener=' + pSession + '&prefix=' + pPrefix + '&dir=' + customDirectory() + '&ses='+ pSession + '&form_ses='+ pFormSession + '&inframe=1';
	toggleModalMode();
	var id                     = addAppendChild(document.body,'iframe', 'nuIBrowse');
	id.style.position          = 'absolute';
	id.className               = 'nuLookupFrame';
	id.style.opacity           = 1;
	id.style.backgroundColor   = "white";
	id.src                     = vURL;
	return true;

}



function openForm(pFormID, pRecordID, pFly, pBrowseFilter){

//-- pBrowseFilter is "searchString" from the calling browse screen
	var fly               = '';
	if(pFly == '1'){
		fly               = '1';  //-- if == 1 it means its being added on the fly from a Lookup Browse Form
	}
	if(pFly == '2'){
		fly               = '2';  //-- if == 2 it means its being added/edited on the fly from a Browse Subform
	}

	if(nuControlKey || !nuSingleWindow() || isNuSubform()){  
		var newwin = '1';
	}else{
		var newwin = '0';
	}
	
	
    if (typeof pBrowseFilter == 'undefined'){
		pBrowseFilter = '';
	}

	if(pRecordID.search("#") != -1){
		while(pRecordID.search("#") != -1){
			pRecordID                = pRecordID.replace("#", "");
		}
		if(document.getElementById(pRecordID)){  //-- valid id
			pRecordID = document.getElementById(pRecordID).value;
		}else{
			pRecordID = '';
		}
	}
	
	var vURL              = "form.php?x=1&f=" + pFormID + "&r=" + pRecordID + '&dir=' + customDirectory() + '&fly=' + fly + '&ses=' + session_id() + '&BF=' + pBrowseFilter + '&newwin=' + newwin;
	if(pRecordID == -1){
		pRecordID         = 'newrec';
	}
	var uName             = '_self';

	if(nuControlKey || !nuSingleWindow() || isNuSubform()){  
		uName             = 'F'+pFormID+safeCustomDirectory()+pRecordID;
	}else{
		vURL              = vURL + addHistory();
	}
	nuControlKey          = false;
    nuFirstClick          = false;
	e                     = window.open(vURL, uName ,maximumScreen());
	e.focus();
	return true;

}


function addHistory(){

	if(nuControlKey){  
		ts                     = new Date()
		nuHistorySession       = ts.getTime();
		nuHistoryIndex         = -1;
	}
	if(document.forms[0].name == 'theform'){
		var fType              = 'Edit|||' + document.title;
	}else{
		var fType              = 'Browse|||' + document.title;
	}
	if(document.title == 'INDEX'){
		var fType              = '|||'+nu_lang['HOME'];
	}
	nuHistoryIndex             = Number(nuHistoryIndex) + 1 ;
	var ref                    = window.location.href;
	ref                        = ref + '&tab=' + nuLastTab;

	if(ref.search("&tab=0&tab=0") != -1){
		while(ref.search("&tab=0&tab=0") != -1){
			ref                = ref.replace("&tab=0&tab=0", "&tab=0");
		}
	}
	
	
	


	return '&historySession='  + nuHistorySession + '&historyIndex=' + nuHistoryIndex + '&historyLocation=' + Base64.encode(fType) + '|||' + Base64.encode(ref);
//	return '&historySession='  + nuHistorySession + '&historyIndex=' + nuHistoryIndex + '&historyLocation=' + Base64.encode(fType) + '|||' + Base64.encode(window.location.href + '&tab=' + nuLastTab);
}




function keyUpEvent(e){

	if(!e){e=window.event;}

	if(e.keyCode == 16){  //-- shift key
		nuShiftKey  = false;
	}
	if(!e){e=window.event;}
	if(e.keyCode == 17){  //-- control key
		nuControlKey   = false;
	}
}

	function keyDownEvent(e){
	
		if(!e){e=window.event;}
		
		if(e.keyCode == 16){  	//-- shift key
			nuShiftKey  = true;
		}
		if(e.keyCode == 17){ 	//-- control key
			nuControlKey   = true;
		}
	}



function openHelp(pFormID){

	var vURL              = "formhelp.php?x=1&f=" + pFormID + '&dir=' + customDirectory();
	e                     = window.open(vURL,'E'+pFormID+safeCustomDirectory(),maximumScreen());
	e.focus();
	return true;

}


function right(pString, pLength){
   return pString.substr(pString.length - pLength);
}




function validateLU(pRecordID, pPrefix, pNewID, pFormSession, pThis, force){
    if (typeof force == 'undefined') force = false;
    try{
        if (typeof pThis == 'undefined' || (typeof pThis != 'undefined' && (pThis.value == '' || ($(pThis).attr('autocomplete') == 'off')))) {
            chooseLUEvent(pRecordID, pPrefix, pNewID, pFormSession,force);
        }
    } catch (e){
        alert('An error occurred while checking the lookup');
    }
}


function chooseLUEvent(pRecordID, pPrefix, pNewID, pFormSession, force){

    var par   = 'r='          + pRecordID
             + '&p='         + pPrefix
             + '&o='         + 'code'
             + '&n='         + pNewID
             + '&dir='       + customDirectory()
             + '&form_ses='  + form_session_id()
             + '&ses='       + session_id()
    ,xhr = ajaxObject();

    if(!xhr){return;}

    xhr.open("POST", "browseduplicates.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.setRequestHeader("Content-length", par.length);
    xhr.setRequestHeader("Connection", "close");
    xhr.onreadystatechange = function(){
        if(xhr.readyState == 4){
            ok = xhr.responseText;
            if(ok != 1){  //---- more than one record with that code
                getOneLookupRecord(pRecordID, pPrefix, '', pFormSession);
                //--- CAM 21-10-2011 - disable auto-browse when no match found
                if (pNewID != '' && force){
					nuControlKey=true;
					openBrowse(pRecordID, '', pPrefix, session_id(), pFormSession, '', pNewID);
				}
            }else{
                getOneLookupRecord(pRecordID, pPrefix, pNewID, pFormSession);
            }
        }
    };
    xhr.send(par);
}





function getOneLookupRecord(pRecordID, pPrefix, pNewID, pFormSession){

   var par   = new String();
   par       = 'r='          + pRecordID;
   par      += '&p='         + pPrefix;
   par      += '&o='         + 'code';
   par      += '&n='         + pNewID;
   par      += '&dir='       + customDirectory();
   par      += '&form_ses='  + form_session_id();
   par      += '&ses='       + session_id();

   var xhr;
   xhr = ajaxObject();
   
   if(!xhr){return;}

   xhr.open("POST", "formlookup.php", true);
   xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   xhr.setRequestHeader("Content-length", par.length);
   xhr.setRequestHeader("Connection", "close");
   xhr.onreadystatechange = function(){
      if(xhr.readyState == 4){
    	 handleLUXML(xhr.responseXML.documentElement);
      }
   };
   xhr.send(par);

}


function getRecordFromList(pRecordID, pPrefix, pNewID){

   var par   = 'r='          + pRecordID
             + '&p='         + pPrefix
             + '&o='         + 'id'
             + '&n='         + pNewID
             + '&dir='       + customDirectory()
             + '&form_ses='  + form_session_id()
             + '&ses='       + session_id()
   ,xhr = ajaxObject()
   ,windowContext = window.isEditScreen ? window : window.opener;
   
   if(!xhr){return;}

   xhr.open("POST", "formlookup.php", true);
   xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   xhr.setRequestHeader("Content-length", par.length);
   xhr.setRequestHeader("Connection", "close");
   if(xhr.overrideMimeType){
	   xhr.overrideMimeType("text/xml");
   }
   xhr.onreadystatechange = function(){
      if(xhr.readyState   == 4){
        if (windowContext) windowContext.handleLUXML(xhr.responseXML.documentElement);
        if (!window.isEditScreen) {
//            try { window.opener.parent.focus(); } catch (e) {}
            window.close();
        }
      }
   };
   xhr.send(par);
}



function getRecordFromIframeList(pNewID){

	pRecordID = frameLookFor();
	pPrefix   = frameRowPrefix();
	
	$.post('formlookup.php'
		,{   p        : pPrefix
			,o        : 'id'
			,r        : pRecordID
			,n        : pNewID
			,dir      : customDirectory()
			,form_ses : form_session_id()
			,ses      : session_id()
		}
		,function(data, textStatus, jqXHR ){
			if (textStatus === "success"){
				parent.window.handleLUXML(data);
				if(parent.document.getElementById('nuIBrowse')){
					parent.window.nuControlKey=false;
					parent.window.toggleModalMode();
					parent.document.getElementById('nuIBrowse').parentNode.removeChild(parent.document.getElementById('nuIBrowse'));
				}
			}
		}
		,"xml"
	);
}


function handleLUXML(rootnode){

		var subformrowList = rootnode.getElementsByTagName("subformrow");
		for(var i = 0; i < subformrowList.length; i++){
			var cur = "";
			if(subformrowList[0].childNodes[0]){
				cur = decodeURIComponent(subformrowList[0].childNodes[0].nodeValue);
			}
			if(document.getElementById(cur)){  //-- if its on a subform that has checkboxes
				document.getElementById(cur).checked = false;
			}
		}
		//these are the lookup's values to be changed
		var attributeList = rootnode.getElementsByTagName("attribute");
		for(var i = 0; i < attributeList.length; i++){
			var key = "";
			var val = "";
			var cur = attributeList[i].getElementsByTagName("key");
			if(cur[0].childNodes[0]){
				key = decodeURIComponent(cur[0].childNodes[0].nodeValue);
			}
			var cur = attributeList[i].getElementsByTagName("value");
			if(cur[0].childNodes[0]){
				val = decodeURIComponent(cur[0].childNodes[0].nodeValue);
			}
			if(document.getElementById(key)){
				document.getElementById(key).value = val;
				if (document.getElementById('z___' + key))
					document.getElementById('z___' + key).value = '';

			}
		}
		//these are any additional things to be changed, as specified with $updateField[]
		var updateList = rootnode.getElementsByTagName("update");
		for(var i = 0; i < updateList.length; i++){
			var key = "";
			var val = "";
			var cur = updateList[i].getElementsByTagName("key");
			if(cur[0].childNodes[0]){
				key = decodeURIComponent(cur[0].childNodes[0].nodeValue);
			}
			var cur = updateList[i].getElementsByTagName("value");
			if(cur[0].childNodes[0]){
				val = decodeURIComponent(cur[0].childNodes[0].nodeValue);
			}
			if(document.getElementById(key)){
				document.getElementById(key).value = val;
				if(document.getElementById(key).onchange){
					document.getElementById(key).onchange();
				}
			}
		}
		//we want to execute the javascript last, just in case it flops over
		var javascriptList = rootnode.getElementsByTagName("javascript");
		var nuScreen = window;
		var nuLookupField = document.getElementById(decodeURIComponent(attributeList[0].getElementsByTagName("key")[0].childNodes[0].nodeValue));
		try{var nuLookupCodeField = document.getElementById(decodeURIComponent(attributeList[1].getElementsByTagName("key")[0].childNodes[0].nodeValue));}catch(e){}
		try{var nuLookupDescriptionField = document.getElementById(decodeURIComponent(attributeList[2].getElementsByTagName("key")[0].childNodes[0].nodeValue));}catch(e){}
		for(var i = 0; i < javascriptList.length; i++){
			var cur = "";
			if(javascriptList[0].childNodes[0]){
				cur = decodeURIComponent(javascriptList[0].childNodes[0].nodeValue);
			}
			try{
				eval(cur);
			}catch(e){
				alert("An error occured while executing the following code:\n"
						+cur
						+"\nError: "+e.toString()
						+"\n\nYou may wish to contact this page's developer, or your sysadmin.");
			}
		}
	if(document.getElementById('beenedited')){
		document.getElementById('beenedited').value = '1';
	}
}


function SelectAll(pListBox){
  var TheListBox = document.getElementById(pListBox);
  for(i=0;i<TheListBox.length;i++){
  	TheListBox[i].selected=true;
  }
  uDB(TheListBox,'listbox')
}


function nuNumberise(pID){

	var ob = document.getElementById(pID);
	var no = ob.value.split(aSeparator[ob.accept]).join('');
	return Number(no.split(',').join('.'));

}

function nuFormat(pThis){
	var fType             = new String(aType[pThis.accept]);
	var fFormat           = new String(aFormat[pThis.accept]);
	var fDecimal          = new String(aDecimal[pThis.accept]);
	var fSeparator        = new String(aSeparator[pThis.accept]);
	var formattedValue    = pThis.value;
	if(fType         == 'number'){
		formattedValue    = nuFormatNumber(pThis.value, fFormat, fDecimal, fSeparator);
	}
	if(fType         == 'date'){
		formattedValue    = nuFormatDate(pThis.value, fFormat);
	}
	pThis.value = formattedValue;
}


function nuFormatNumber(pValue, pDecimalPlaces, pDecimal, pSeparator){

//-- this function allows a valid number to be passed and formatted even if its format type is supposed to be Euro.
//-- (which is an invalid number according to javascript)

	if(pValue==''){return '';}
	
	var theNumber   = '';
	if(pDecimal     == ','){                             //-- is Euro
		if(isNaN(pValue)){                               //-- it won't have any separators to remove
			theNumber = pValue.split(".").join('');      //-- remove separators
			theNumber = theNumber.split(",").join('.');  //-- swap to a decimal point
			if(isNaN(theNumber)){
				return '';
			}else{
				return nuRebuildNumber(theNumber, pDecimalPlaces, pDecimal, pSeparator);
			}
		}else{
			return nuRebuildNumber(pValue, pDecimalPlaces, pDecimal, pSeparator);
		}
	}else{
		theNumber   = pValue.split(",").join('');        //-- remove separators
		if(isNaN(theNumber)){
			return '';
		}else{
			return nuRebuildNumber(theNumber, pDecimalPlaces, pDecimal, pSeparator);
		}
		
	}

}

function nuRebuildNumber(pValue, pDecimalPlaces, pDecimal, pSeparator){

	var formattedNumber = Number(pValue).toFixed(pDecimalPlaces);
	var theSplit        = formattedNumber.split('.');
	var theInt          = theSplit[0];  //-- whole number
	var theFra          = theSplit[1];  //-- fraction
	var newInt          = '';
	var c               = 0;

	//---format whole portion of number
	for(var i = theInt.length ; i > 0 ; i--){
	   if(c == 3 || c == 6 || c == 9 || c == 12 || c == 15){
	      if(theInt[i-1] != '-'){                           //---if not a minus number
	         newInt    = pSeparator + newInt;
	      }
	   }
	   c               = c + 1;
	   newInt          = theInt[i-1] + newInt;
	}

	if(theSplit.length == 2){
		return newInt + pDecimal + theFra;
	}else{
		return newInt;
	}

}




function nuFormatNumber_old(pValue, pDecimalPlaces, pDecimal, pSeparator){

	if(pValue==''){return '';}
	var splitWhole        = new Array();
	var splitPart         = new Array();
	var halve             = new Array();
	var divYear           = new Array();
	var c                 = 0;
	var whole             = new String();
	var part              = new String();
	var nn                = '';
	var addOne            = false;
	var newValue          = new String(pValue);
	if(pSeparator!=''){

		while(newValue.indexOf(pSeparator)!=-1){
			newValue          = newValue.replace(pSeparator,'');     //---remove separators
		}
	}
	newValue              = newValue.replace(pDecimal,'.');    //---make sure decimal is '.'
	// Don't allow if the input (minus separators) is not a number
	if(isNaN(newValue)){return '';}
	
	// Apply correct rounding
	newValue = String(Math.round(Number(newValue) * Math.pow(10, pDecimalPlaces)) / Math.pow(10, pDecimalPlaces));
	
	var halve             = newValue.split('.');                //---split whole and part
	splitWhole            = halve[0].split('');
	if(halve.length==2){
		splitPart         = halve[1].split('');
	}

	//---format whole portion of number
	for (var i=splitWhole.length ; i>0 ; i--){
	   if(c==3||c==6||c==9||c==12||c==15){
	      if(splitWhole[i-1]!='-'){                           //---if not a minus number
	         whole = pSeparator+whole;
	      }
	   }
	   c   = c + 1;
	   whole = splitWhole[i-1]+whole;
	}

	//---format part portion of number
	for (i=0 ; i < splitPart.length ; i++){
		nn = splitPart[i];
		if(i < pDecimalPlaces){
			part = part+''+nn;
		}
	}

	while(part.length < pDecimalPlaces){
		part = part+''+'0';
	}

	if(pDecimalPlaces==0){
		return whole;
	}else{
		return whole + pDecimal + part;
	}

}



function nuFormatDate(pValue, pFormat){


	if(String(pValue).length == 0){
		return '';
	}

	var split             = new Array();
	var dd                = new String();
	var mm                = new String();
	var yy                = new String();
	var fdd               = new String();
	var fmm               = new String();
	var fyy               = new String();
	var strDay            = new String();
	var strMth            = new String();	
	var strYr             = new String();
	var d                 = new Date();
	var US                = new Boolean();
	US                    = pFormat.substr(0,1)=='m';

	var strTwoChr     = new Array();
	strTwoChr[1]      = '01';
	strTwoChr[2]      = '02';
	strTwoChr[3]      = '03';
	strTwoChr[4]      = '04';
	strTwoChr[5]      = '05';
	strTwoChr[6]      = '06';
	strTwoChr[7]      = '07';
	strTwoChr[8]      = '08';
	strTwoChr[9]      = '09';
	strTwoChr[10]     = '10';
	strTwoChr[11]     = '11';
	strTwoChr[12]     = '12';
	strTwoChr[13]     = '13';
	strTwoChr[14]     = '14';
	strTwoChr[15]     = '15';
	strTwoChr[16]     = '16';
	strTwoChr[17]     = '17';
	strTwoChr[18]     = '18';
	strTwoChr[19]     = '19';
	strTwoChr[20]     = '20';
	strTwoChr[21]     = '21';
	strTwoChr[22]     = '22';
	strTwoChr[23]     = '23';
	strTwoChr[24]     = '24';
	strTwoChr[25]     = '25';
	strTwoChr[26]     = '26';
	strTwoChr[27]     = '27';
	strTwoChr[28]     = '28';
	strTwoChr[29]     = '29';
	strTwoChr[30]     = '30';
	strTwoChr[31]     = '31';

	var strMonthArray     = new Array();
	strMonthArray[1]      = 'Jan';
	strMonthArray[2]      = 'Feb';
	strMonthArray[3]      = 'Mar';
	strMonthArray[4]      = 'Apr';
	strMonthArray[5]      = 'May';
	strMonthArray[6]      = 'Jun';
	strMonthArray[7]      = 'Jul';
	strMonthArray[8]      = 'Aug';
	strMonthArray[9]      = 'Sep';
	strMonthArray[10]     = 'Oct';
	strMonthArray[11]     = 'Nov';
	strMonthArray[12]     = 'Dec';

	var numMonthArray     = new Array();
	numMonthArray['jan']  = 1;
	numMonthArray['feb']  = 2;
	numMonthArray['mar']  = 3;
	numMonthArray['apr']  = 4;
	numMonthArray['may']  = 5;
	numMonthArray['jun']  = 6;
	numMonthArray['jul']  = 7;
	numMonthArray['aug']  = 8;
	numMonthArray['sep']  = 9;
	numMonthArray['oct']  = 10;
	numMonthArray['nov']  = 11;
	numMonthArray['dec']  = 12;

	//---split date by '/' or '-' or '.'
	if(pValue.indexOf('/')!=-1){split = pValue.split('/');}
	if(pValue.indexOf('-')!=-1){split = pValue.split('-');}
	if(pValue.indexOf('.')!=-1){split = pValue.split('.');}

	if(split.length < 2){
		alert('Invalid Date..');
		return '';
	}
	if(String(split[0]).length == 0 || String(split[1]).length == 0){
		alert('Invalid Date..');
		return '';
	}
	//---add year if needed
	if(split.length == 2){
		split[2]    = d.getFullYear();
	}
	splitFormat   = pFormat.split('-');

	if(US){ //---is USA date
		dd   = split[1];
		mm   = split[0];
		yy   = split[2];
		fdd  = splitFormat[1];
		fmm  = splitFormat[0];
		fyy  = splitFormat[2];
	}else{
		dd   = split[0];
		mm   = split[1];
		yy   = split[2];
		fdd  = splitFormat[0];
		fmm  = splitFormat[1];
		fyy  = splitFormat[2];
	}
	if(String(mm).length  == 3){                            //---if month is 3 characters long
		mm        = numMonthArray[mm.toLowerCase()];        //---swap to month number
	}
	if(String(yy).length  != 4){                            //---if year is 4 characters long
		yy        = 2000+Number(yy);                        //---swap to year number
	}

	if(Number(dd) > 31){
		alert('Invalid Date..');
		return '';
	}
	if(Number(dd) > 30 && (Number(mm)==2||Number(mm)==4||Number(mm)==6||Number(mm)==9||Number(mm)==11)){
		alert('Invalid Date..');
		return '';
	}
// 14/01/09 - Edited by Jeff, Nick, Michael, and everyone else --> 29/1/0x returns an invalid date
	if(Number(dd) == 29 && Number(mm)==2){
		divYear = String(Number(yy)/4).split('.');
		if(Number(yy)/4!=Number(divYear[0])){
			alert('Invalid Date..');
			return '';
		}
		divYear = String(Number(yy)/100).split('.');
		if(Number(yy)/100==Number(divYear[0]) && Number(yy) != 1600  && Number(yy) != 2000  && Number(yy) != 2400 ){
			alert('Invalid Date..');
			return '';
		}

	}
	d.setDate(1);
	d.setFullYear(Number(yy));
	d.setMonth(Number(mm)-1); //--month numbers start at 0 (11 = december)
	d.setDate(Number(dd));

	if(d.getDate()!=Number(dd) || d.getMonth()!=Number(mm)-1 || d.getFullYear()!=Number(yy)){
		alert('Invalid Date..');
		return '';
	}

	strDay           = strTwoChr[Number(dd)];       //---convert to 2 characters
	if(fmm.length    == 3){
		strMth       = strMonthArray[Number(mm)];   //---convert to 3 characters
	}else{
		strMth       = strTwoChr[Number(mm)];       //---convert to 2 characters
	}
	if(fyy.length    == 4){
		strYr        = yy;                  //---convert to 4 characters
	}else{
		strYr        = String(yy).substr(2);        //---convert to 2 characters
	}

	if(US){ //---is USA date
		return strMth+'-'+strDay+'-'+strYr;
	}else{
		return strDay+'-'+strMth+'-'+strYr;
	}

}



//=========cookie functions===========================

function nuCreateCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+safeCustomDirectory()+"="+value+expires+"; path=/";
}

function nuReadCookie(name) {
	var nameEQ = name +safeCustomDirectory() +"=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function nuEraseCookie(name) {
	nuCreateCookie(name,"",-1);
}

function safeCustomDirectory(){
	return customDirectory().replace(/\//gi,"");
}

//=========end of cookie functions====================



//=========start of helper functions====================

// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || []; // store logs to an array for reference
  log.history.push(arguments);
  if(this.console) {
    arguments.callee = arguments.callee.caller;
    var newarr = [].slice.call(arguments);
    (typeof console.log === 'object' ? log.apply.call(console.log, console, newarr) : console.log.apply(console, newarr));
  }
};

// make it safe to use console.log always
(function(b){function c(){}for(var d="assert,clear,count,debug,dir,dirxml,error,exception,firebug,group,groupCollapsed,groupEnd,info,log,memoryProfile,memoryProfileEnd,profile,profileEnd,table,time,timeEnd,timeStamp,trace,warn".split(","),a;a=d.pop();){b[a]=b[a]||c}})((function(){try
{console.log();return window.console;}catch(err){return window.console={};}})());

//=========start of helper functions====================



/**
  * Autocomplete setup script
  * Added: 21-10-2011 
  * Requires jquery-ui and jquery
  *
  * To apply a jquery-ui theme, just link to the appropriate stylesheet in form.php
  * (You may need to remove some css rules from css/core.css)
  *
  **/

if (typeof jQuery != 'undefined') {
    $(function() {
        var FormSession = $('#formsessionID').val()
            ,Session = $('#session_id').val()
            ,customDirectory = $('#customDirectory').val();

        window.setupAutoComplete = function() {
            var cache = {}
            ,lastXhr
            ,query
            ,lookin
            ,theField = $(this)
            ,subformRow = theField.closest('.subform-row')
            ,subPrefix = subformRow.length ? subformRow.attr('id').replace(/^rowdiv_/,'') : ''
            ,itemSelected = false
            ,itemLookingUp = false;
            
            if (theField.hasClass('autocomplete-configured')) return;
            if (!theField.hasClass('autocomplete')) {
                theField.change(function(){validateLU(theField.attr('accept'), subPrefix, theField.val(), FormSession);});
                return;
            }
            
            theField.change(function() {
                        setTimeout(function() {
                                if (!itemLookingUp && !itemSelected) {
                                    validateLU(theField.attr('accept'), subPrefix, theField.val(), FormSession, theField[0]);
                                    theField.autocomplete('close');
                                }
                            }
                        ,300);
                        return true;
                    }
                )
                .focus(function() {
                        itemSelected = false;
                        itemLookingUp = false;
                        return true;
                    }
                )
                .autocomplete({
                    minLength: 3,
                    autoFocus: false,
                    delay: 300,
                    source: function( request, response ) {
                        var term = request.term
                            ,subform;
                        if ( term in cache && !itemSelected) {
                            response( cache[ term ] );
                            return;
                        }
                        request.dir = customDirectory;
                        request.form_ses = FormSession;
                        request.o = 'code';
                        request.r = theField.attr('accept');
                        request.p = subPrefix;
                        request.ses = session_id();
                        
                        lastXhr = $.getJSON( 
                            "autocomplete.php"
                            ,request
                            ,function( data, status, xhr ) {
                                if (data.SUCCESS) {
                                    cache[ term ] = data['DATA']['results'];
                                    if ( xhr === lastXhr ) {
                                        response( data['DATA']['results'] );
                                    }
                                } else {
                                    log('Error returned from autocomplete',data.ERRORS[0]);
                                }
                            });
                        }
                    ,focus: function( event, ui ) {
                        return false;
                    }
                    ,select: function( event, ui ) {
                        if (ui.item.id == '') {
                            itemLookingUp = true;
                            validateLU(theField.attr('accept'), subPrefix, theField.val(), FormSession, theField[0], true);
                        } else {
                            itemSelected = true;
                            getRecordFromList(theField.attr('accept'), subPrefix, ui.item.id);
                        }
                        return false;
                    }
                    ,open: function() {
                        itemSelected = false;
                        itemLookingUp = false;
                    }
                    ,change: function(event,ui) {
                        if ((!itemSelected && !itemLookingUp) || theField.val() == '') { 
                            itemLookingUp = true;
                            validateLU(theField.attr('accept'), subPrefix, theField.val(), FormSession, theField[0]);
                        }
                    }
                })
                .data( "autocomplete" )._renderItem = function( ul, item ) {
                    return $( "<li></li>" )
                        .data( "item.autocomplete", item )
                        .append( "<a>" + item.code + (typeof item.name == 'undefined' || item.name == '' ? '' : " - " + item.name) + "</a>" )
                        .appendTo( ul );
                }
            ;
            theField.addClass('autocomplete-configured');
        };
        $(".lookupcode").each(setupAutoComplete);
    });
}


function gotoNuHistory(pIndex){

	if(nuHistoryArray[pIndex] == ''){
		openForm('index', '-1');
	}else{
		window.location=Base64.decode(nuHistoryArray[pIndex].split('|||')[1]);
	}

}

//++++++++++++++++++++++++++++++

var Base64 = {

// private property
_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

// public method for encoding
encode : function (input) {
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;

    input = Base64._utf8_encode(input);

    while (i < input.length) {

        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }

        output = output +
        this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
        this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

    }

    return output;
},

// public method for decoding
decode : function (input) {
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;

    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    while (i < input.length) {

        enc1 = this._keyStr.indexOf(input.charAt(i++));
        enc2 = this._keyStr.indexOf(input.charAt(i++));
        enc3 = this._keyStr.indexOf(input.charAt(i++));
        enc4 = this._keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }

    }

    output = Base64._utf8_decode(output);

    return output;

},

// private method for UTF-8 encoding
_utf8_encode : function (string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {

        var c = string.charCodeAt(n);

        if (c < 128) {
            utftext += String.fromCharCode(c);
        }
        else if((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        }
        else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }

    }

    return utftext;
},

// private method for UTF-8 decoding
_utf8_decode : function (utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

        c = utftext.charCodeAt(i);

        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }

    }

    return string;
}

}

//++++++++++++++++++++++++++++++


//=========== move iFrame ==============================


function mouseMoveIF(e){  //-- used in Browse iFrame

	if(mouseIsDownIF){
		if(!e){e      = window.event;}
		var f         = parent.document.getElementById('nuIBrowse');
		f.style.left  = ((parseInt(e.clientX) - parseInt(offsetXIF)) + parseInt(f.style.left)) + 'px';
		f.style.top   = ((parseInt(e.clientY) - parseInt(offsetYIF)) + parseInt(f.style.top)) + 'px';
	}
	
}

function mouseDnIF(e){  //-- used in Browse iFrame

	if(!e){e         = window.event;}
	var f            = parent.document.getElementById('nuIBrowse');
	
	mouseIsDownIF    = true;
	offsetXIF        = parseInt(e.clientX);
	offsetYIF        = parseInt(e.clientY);

	if(f.style.left == ''){
		f.style.left = nuFrameLeft();
		f.style.top  = nuFrameTop();
	}
	

}

function mouseUpIF(){  //-- used in Browse iFrame

	shiftKeyIF        = false;
	mouseIsDownIF     = false;

}
//====================================================nuLookupFrame


function nuMoveObject(pID, pTop, pLeft) {         //-- added by sc 12th Nov 2012

	var theid = pID;
	var isSF  = false;
	if($('#'+theid).length == 0){return;}
	if($('#moved_' + theid).length != 0){return;} //-- its already been moved
	if(nuSystemForm()){return;}                   //-- do not allow chaning system Forms

	if (theid.substr(15) == 'sf_inner_title'){             //-- subform - when being clicked on originally
		isSF  = true;
		theid = theid.substr(0,14);
	}
	if ($('#'+theid+'_sf_inner_title').length != 0){        //-- subform - when being loaded
		isSF  = true;
	}
	
	var to    = parseInt(pTop);
	var le    = parseInt(pLeft);
	var div   = '<div id="moved_' + theid;
	div       = div + '" style="position:absolute;top:';
	div       = div + to + 'px;left:' + le + 'px"><table><tr>';
	div       = div + '<td id="left_'  + theid + '" class="selected" style="text-align:right"></td>';
	div       = div + '<td id="center_' + theid + '" class="selected" style="text-align:left;white-space: nowrap;"></td>';
	if(access_level() == 'globeadmin'){
		div       = div + '<td id="right_'  + theid + '" class="selected" style="background-color:transparent;text-align:left"><img class="nuDrag" style="opacity:0.2;" src="dragger.png" alt="Move Object"></td>';
	}
	div       = div + '</tr></table></div>';

	$('#'+$('#'+theid).closest("[id^=MidDiv]").attr('id')).append(div);
	if($('#sel_'+theid).length != 0){                      //-- listbox
		$('#center_' + theid).append($('#sel_'+theid));
		$('#center_' + theid).append('<br>');
	}

	if($('#ifr_'+theid).length != 0){                      //-- browse subform
		$('#' + theid).css({ position: 'relative', top: 0, left: 0});
		$('#ifr_' + theid).css({ position: 'relative', top: 0, left: 0});
		$('#center_' + theid).append($('#'+theid));
		$('#center_' + theid).append($('#ifr_'+theid));
	}else if (isSF){                                       //-- subform
		$('#sf_title'+theid).css({ position: 'relative', top: 0, left: 0});
		$('#' + theid).css({ position: 'relative', top: 0, left: 0});
		$('#center_' + theid).append($('#sf_title'+theid));
		$('#center_' + theid).append($('#'+theid));
	}else{
		$('#center_' + theid).append($('#'+theid));
	}

	if($('#html_'+theid).length != 0){                     //-- html object
		$('#center_' + theid).append($('#html_'+theid));
	}
	if($('#'+theid+'_title').length != 0){
		$('#left_'  + theid).append($('#'+theid+'_title'));
	}
	if($('#code'+theid).length != 0){                      //-- lookup code
		$('#center_' + theid).append($('#code'+theid));
	}
	if($('#luup_'+theid).length != 0){                     //-- lookup button
		$('#center_' + theid).append($('#luup_'+theid));
	}
	if($('#description'+theid).length != 0){               //-- lookup description
		$('#center_' + theid).append($('#description'+theid));
	}
	if($('#cal_'+theid).length != 0){                      //-- calendar button
		$('#center_' + theid).append($('#cal_'+theid));
	}
	if($('#delete_file_'+theid).length != 0){              //-- part of File Object
		$('#center_' + theid).append($('#delete_file_'+theid));
	}

	if(access_level() == 'globeadmin'){
		$('#moved_' + theid).draggable({stop: function() { 
		var m = $('#nuMoved');
		var o = $('#moved_' + theid);
		var p = o.position();
		m.val(m.val()+'|'+o.attr('id').substring(6)+','+p.top+','+p.left); 
		}});
	}

}


