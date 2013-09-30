<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head> 
<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>
<title>nuBuilder Report Writer</title>
<--! Corrupt File -->  
<style>
/*
 * HTML5 ? Boilerplate
 *
 * What follows is the result of much research on cross-browser styling. 
 * Credit left inline and big thanks to Nicolas Gallagher, Jonathan Neal,
 * Kroc Camen, and the H5BP dev community and team.
 */


/* =============================================================================
   HTML5 element display
   ========================================================================== */

article, aside, details, figcaption, figure, footer, header, hgroup, nav, section { display: block; }
audio[controls], canvas, video { display: inline-block; *display: inline; *zoom: 1; }


/* =============================================================================
   Base
   ========================================================================== */

/*
 * 1. Correct text resizing oddly in IE6/7 when body font-size is set using em units
 *    http://clagnut.com/blog/348/#c790
 * 2. Force vertical scrollbar in non-IE
 * 3. Remove Android and iOS tap highlight color to prevent entire container being highlighted
 *    www.yuiblog.com/blog/2010/10/01/quick-tip-customizing-the-mobile-safari-tap-highlight-color/
 * 4. Prevent iOS text size adjust on device orientation change, without disabling user zoom
 *    www.456bereastreet.com/archive/201012/controlling_text_size_in_safari_for_ios_without_disabling_user_zoom/
 */

html { font-size: 100%; overflow-y: scroll; -webkit-tap-highlight-color: rgba(0,0,0,0); -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }

body { margin: 0; font-size: 13px; line-height: 1.231; }

body, button, input, select, textarea { font-family: sans-serif; color: #222; }

/* 
 * These selection declarations have to be separate
 * No text-shadow: twitter.com/miketaylr/status/12228805301
 * Also: hot pink!
 */

::-moz-selection { background: #fe57a1; color: #fff; text-shadow: none; }
::selection { background: #fe57a1; color: #fff; text-shadow: none; }


/* =============================================================================
   Links
   ========================================================================== */

a { color: #00e; }
a:visited { color: #551a8b; }
a:focus { outline: thin dotted; }

/* Improve readability when focused and hovered in all browsers: people.opera.com/patrickl/experiments/keyboard/test */
a:hover, a:active { outline: 0; }


/* =============================================================================
   Typography
   ========================================================================== */

abbr[title] { border-bottom: 1px dotted; }

b, strong { font-weight: bold; }

blockquote { margin: 1em 40px; }

dfn { font-style: italic; }

hr { display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0; }

ins { background: #ff9; color: #000; text-decoration: none; }

mark { background: #ff0; color: #000; font-style: italic; font-weight: bold; }

/* Redeclare monospace font family: en.wikipedia.org/wiki/User:Davidgothberg/Test59 */
pre, code, kbd, samp { font-family: monospace, monospace; _font-family: 'courier new', monospace; font-size: 1em; }

/* Improve readability of pre-formatted text in all browsers */
pre { white-space: pre; white-space: pre-wrap; word-wrap: break-word; }

q { quotes: none; }
q:before, q:after { content: ""; content: none; }

small { font-size: 85%; }

/* Position subscript and superscript content without affecting line-height: gist.github.com/413930 */
sub, sup { font-size: 75%; line-height: 0; position: relative; vertical-align: baseline; }
sup { top: -0.5em; }
sub { bottom: -0.25em; }


/* =============================================================================
   Lists
   ========================================================================== */

ul, ol { margin: 1em 0; padding: 0 0 0 40px; }
dd { margin: 0 0 0 40px; }
nav ul, nav ol { list-style: none; margin: 0; padding: 0; }


/* =============================================================================
   Embedded content
   ========================================================================== */

/*
 * 1. Improve image quality when scaled in IE7 http://h5bp.com/d
 * 2. Remove the gap between images and borders on image containers http://h5bp.com/e 
 */

img { border: 0; -ms-interpolation-mode: bicubic; vertical-align: middle; }

/*
 * Correct overflow displayed oddly in IE9 
 */

svg:not(:root) { overflow: hidden; }


/* =============================================================================
   Figures
   ========================================================================== */

figure { margin: 0; }


/* =============================================================================
   Forms
   ========================================================================== */

form { margin: 0; }
fieldset { border: 0; margin: 0; padding: 0; }

/* 
 * 1. Correct color not inheriting in IE6/7/8/9 
 * 2. Correct alignment displayed oddly in IE6/7 
 */

legend { border: 0; *margin-left: -7px; padding: 0; }

/* Indicate that 'label' will shift focus to the associated form element */
label { cursor: pointer; }

/*
 * 1. Correct font-size not inheriting in all browsers
 * 2. Remove margins in FF3/4 S5 Chrome
 * 3. Define consistent vertical alignment display in all browsers
 */

button, input, select, textarea { font-size: 100%; margin: 0; vertical-align: baseline; *vertical-align: middle; }

/*
 * 1. Define line-height as normal to match FF3/4 (set using !important in the UA stylesheet)
 * 2. Correct inner spacing displayed oddly in IE6/7
 */

button, input { line-height: normal; *overflow: visible; }

/*
 * 1. Display hand cursor for clickable form elements
 * 2. Allow styling of clickable form elements in iOS
 */

button, input[type="button"], input[type="reset"], input[type="submit"] { cursor: pointer; -webkit-appearance: button; }

/*
 * Consistent box sizing and appearance
 */

input[type="checkbox"], input[type="radio"] { box-sizing: border-box; }
input[type="search"] { -moz-box-sizing: content-box; -webkit-box-sizing: content-box; box-sizing: content-box; }

/* 
 * Remove inner padding and border in FF3/4
 * www.sitepen.com/blog/2008/05/14/the-devils-in-the-details-fixing-dojos-toolbar-buttons/ 
 */

button::-moz-focus-inner, input::-moz-focus-inner { border: 0; padding: 0; }

/* Remove default vertical scrollbar in IE6/7/8/9 */
textarea { overflow: auto; vertical-align: top; }

/* Colors for form validity */
input:valid, textarea:valid {  }
input:invalid, textarea:invalid { background-color: #f0dddd; }


/* =============================================================================
   Tables
   ========================================================================== */

table { border-collapse: collapse; border-spacing: 0; }


/* =============================================================================
   Primary styles
   Author: 
   ========================================================================== */








body              {family-font:arial;background-color:lightgrey}
table             {margin-width:0px;border-width:0px;padding-width:0px;text-align:center}
tr                {margin-width:0px;border-width:0px;padding-width:0px}
td                {margin-width:0px;border-width:0px;padding-width:0px}
.nuDialog         {visibility:hidden;overflow:hidden;position:absolute;z-index:1;font-size:14px;border-width:1px;border-style:solid;border-color:black;background-color: gray;height:350px;width:450px;}
.nuClose          {position:absolute;left:0px;height:22px;font-family:arial;font-weight:bold;font-size:18px;border-width:0px;border-style:none;color:white;background-color:black;}
.nuTitle          {position:absolute;left:15px;height:22px;font-family:arial;font-size:18px;border-width:0px;border-style:none;color:white;background-color:black;}
.nuToolbar        {text-align:center;position:absolute;top:22px;font-size:12px;height:20px;width:100px;color:black;background-color:#EBEBEB;border-style:solid;border-color:black;border-width:1px    ;margin-width:0px;padding:0px}
.nuSection        {visibility:visible;position:absolute;overflow:hidden;font-size:14px;border-width:0px;border-style:solid;border-color:black;background-color:#ebebeb;}
.Top_Margin       {position:absolute;border-width:1px;border-style:solid;border-color:white;background-color:white;}
.Left_Margin      {position:absolute;border-width:1px;border-style:solid;border-color:white;background-color:white;}
.nuObject         {}
.sortRight {position:absolute;font-size:14px;color:white;height:10px;left:330px;width:400px}
.sortLeft {position:absolute;font-size:14px;color:white;height:10px;left:40px;width:300px}
div.innerDiv {position:relative;left:0px;width:120px;height:20px;fontSize:16px;	color:#FFFFFF;text-align:left;}
div.objDialog {position:relative;left:10px;top:32px;width:440px;height:27px;fontSize:16px;color:#FFFFFF;}
select.objDialog {position:absolute;top: 2px;left:130px;width:187px;height:26px;}
input.objDialog {position:absolute;top:2px;left:130px;width:180px;height:18px;}
div.sortDialog {position:relative;left:10px;top:32px;width:400px;height:25px;fontSize:16px;color:#FFFFFF;}
input.sortDialog {position:absolute;top:-3px;left:50px;width:150px;height:22px;}
</style>

<script type='text/javascript'>

	var offsetX               = 0;
	var offsetY               = 0;
	var mouseDownX            = 0;
	var mouseDownY            = 0;
	var mouseDownWidth  	  = Array();
	var mouseDownHeight	  	  = Array();
	var sizeX                 = 0;
	var sizeY                 = 0;
	var theID                 = '';
	var theClass              = '';
	var selectMode			  = '';
	var selectedSection		  = '';
	var mouseIsDown           = false;
	var mouseDownWaitComplete = false;
	var shiftKey              = false;
	var ctrlKey               = false;
	var doNotPasteObjects	  = false;
	var multipleSections      = false;
	var startedMoving		  = false;
	var pressedCtrlA		  = false;
	var selectedObjects       = Array();
	var copiedObjects		  = Array();
	var objectProperties      = Array();
	var extraProperties       = Array();
	var reportSection         = Array();
	var reportOrder           = Array();
	var reportID              = Array();
	var sectionNo             = Array();
	var fontFamilies		  = Array();
	var fontWeights			  = Array();
	var radioButtons          = Array();
	var controlArray          = Array();
	var nuOB                  = Array();  //-- array of objects
	var nuSE                  = Array();  //-- array of sections
	var nuHI                  = Array();  //-- array of historical changes
	var objDialogOpen 		  = false;
	var aDialogWasClickedLast = false;
	var reportTop             = 50;
	var lastObjectClicked     = -1;
	var sortWas               = '';

	function nuHistory(pIsObject, pTime, pID, pL, pT, pW, pH){
	
		this.IsObject                        = pIsObject;
		this.Time                            = pTime
		this.ID                              = pID;
		this.Left                            = pL;
		this.Top                             = pT;
		this.Width                           = pW;
		this.Height                          = pH;

		
//--------- set properties -------------------
		this.undoPosition                    = function (){  //-- return object or section to previous position and size
		
			if(this.IsObject){
				var o                        = getObject(this.ID);
				o.setLeft(this.Left);
				o.setTop(this.Top);
				o.setWidth(this.Width);
				o.setHeight(this.Height);
			}else{
				var o                        = getSection(this.ID);
				o.setTop(this.Top);
				o.setWidth(this.Width);
				o.setHeight(this.Height);
			}
		}
		
	}



	function nuSection(pReportIndex){
	
		this.displayIndex                   = nuSE.length;
		this.reportIndex                    = pReportIndex;
		this.id                             = sectionNo[pReportIndex];
		this.section                        = document.getElementById(this.id);
		this.height                         = 0;
		this.width                          = 0;
		this.top                            = 0;
		this.color                          = '#ebebeb';
		this.sortField                      = '';
		this.sectionName                    = '';
		if(this.reportIndex > 4){    //-- is a sorted section
			this.sortIndex                  = Number(this.id.substr(7));
		}else{
			this.sortIndex                  = -1;
		}

		
//--------- set properties -------------------

		this.setSectionProperties                  = function (pHeight, pColor, pName){
			this.setHeight(pHeight);
			this.setColor(pColor);
			this.renameSection(pName);
		}
		
//--------- rename section -------------------

		this.renameSection                         = function (pName){
		
			if(pName == '_Header' || pName == '_Footer'){  //-- no sort name
				pName = '';
			}
			this.sectionName                       = pName;
			this.section.innerHTML                 = pName;
		}
		
//--------- set section width -------------------

		this.setWidth                              = function (pWidth){
			this.width                             = parseInt(pWidth);
			this.section.style.width               = this.width + 'px';
		}
		
//--------- get section width -------------------

		this.getWidth                              = function (){
			return parseInt(this.section.style.width);
		}
		
//--------- get section color -------------------

		this.getColor                              = function (){
			return this.color;
		}
		
//--------- set section color -------------------

		this.setColor                            = function (pNewColor){
			this.color                           = pNewColor;
			this.section.style.backgroundColor   = this.color;
		}
		
//--------- get section top -------------------

		this.getTop                                = function (){
			return parseInt(this.section.style.top);
		}
		
//--------- set section top -------------------

		this.setTop                                = function (pNewTop){
			this.top                               = parseInt(pNewTop);
			this.section.style.top                 = this.top + 'px';
		}
		
//--------- section bottom -------------------

		this.getBottom                             = function (){
			return this.top + this.height;
		}
		
//--------- section right -------------------

		this.getRight                              = function (){
			return this.getLeft() + this.width;
		}
		
//--------- get section left -------------------

		this.getLeft                             = function (){
			return parseInt(this.section.style.left);
		}
		
//--------- get section height -------------------

		this.getHeight                             = function (){
			return this.height;
		}
		
//--------- set section height -------------------

		this.setHeight                             = function (pNewHeight){
			this.height                            = parseInt(pNewHeight);
			this.section.style.height              = this.height + 'px';
		}
		
//--------- adjust section top ---------------

		this.setSectionPosition                    = function (){
		
			var theTop                             = reportTop;

			if(this.displayIndex > 0){
				theTop       = nuSE[this.displayIndex - 1].getBottom();  //--top of previous section
				if(nuSE[this.displayIndex - 1].getHeight() > 0){
					theTop   = theTop + 2;
				}
			}
			this.setTop(theTop);
		}
		
	}
	
	
	
	
	function nuObject(pID, pControlType, pCanGrow, pFormat, pGraph, pBorderWidth, pBorderColor, pBorderStyle, pFontName, pfontWeight){
	
		this.index                    = nuOB.length;
		this.id                       = pID;
		this.sectionID                = '';
		this.ControlType              = pControlType;
		this.CanGrow                  = pCanGrow;
		this.Format                   = pFormat;
		this.fontFamily               = pFontName;
		this.fontWeight               = pfontWeight;
		this.Graph                    = pGraph;
		if(pControlType == 'Graph'){
			this.Graph                = pFormat + '-' + pGraph;
		}
		this.selected                 = false;
		this.inside                   = document.getElementById(this.id);
		this.borderWidth              = parseInt(pBorderWidth);
		this.inside.style.borderWidth = this.borderWidth + 'px ' + this.borderWidth + 'px ' + this.borderWidth + 'px ' + this.borderWidth + 'px';
		this.borderColor              = pBorderColor;
		this.inside.style.borderColor = this.borderColor + ' ' + this.borderColor + ' ' + this.borderColor + ' ' + this.borderColor;
		this.borderStyle              = pBorderStyle;
		this.inside.style.borderStyle = this.borderStyle + ' ' + this.borderStyle + ' ' + this.borderStyle + ' ' + this.borderStyle;
		this.moveLeft                 = 0;
		this.moveTop                  = 0;
		this.moveWidth                = 0;
		this.moveHeight               = 0;
		this.dragStartY               = parseInt(this.inside.style.top);      //-- where dragging started (mouse down)
		this.dragStartX               = parseInt(this.inside.style.left);     //-- where dragging started (mouse down)
		this.dragStartWidth           = parseInt(this.inside.style.width);    //-- where dragging started (mouse down)
		this.dragStartHeight          = parseInt(this.inside.style.height);   //-- where dragging started (mouse down)
		this.offsetX                  = 0;
		this.offsetY                  = 0;

		//--------- maximum left or right movement allowed -------------------

		this.maxMoveX  = function (pL, pR, pMove, pMoveBy){

			var newMoveBy        = pMoveBy;
			if(pMove < pL){                          //-- reduce distance that can be dragged
				newMoveBy        = pMoveBy - (pMove - pL);
				return newMoveBy;
			}
			if(pMove + this.getWidth() > pR){       //-- reduce distance that can be dragged
				newMoveBy        = pMoveBy - ((pMove + this.getWidth()) - pR);
				return newMoveBy;
			}
			return newMoveBy
		}
		
//--------- maximum top or bottom movement allowed -------------------

		this.maxMoveY  = function (pT, pB, pMove, pMoveBy){

			var newMoveBy        = pMoveBy;
			if(pMove < pT){                          //-- reduce distance that can be dragged
				newMoveBy        = pMoveBy - (pMove - pT);
				return newMoveBy;
			}
			if(pMove + this.getHeight() > pB){       //-- reduce distance that can be dragged
				newMoveBy        = pMoveBy - ((pMove + this.getHeight()) - pB);
				return newMoveBy;
			}
			return newMoveBy;
		}
		
//--------- max/min width allowed -----------------------------------

		this.maxSizeX  = function (pR, pSize, pSizeBy, pStartSize){

			var newSizeBy        = pSizeBy;

			if(pSize < 1){return 0-pStartSize;}           //-- reduce distance that can be dragged to minimum

			if(this.getLeft() + pSize > pR){              //-- reduce distance that can be dragged
				newSizeBy        = pR - this.getLeft() - this.dragStartWidth;
				return newSizeBy;
			}
			return newSizeBy;
		}
		
//--------- max/min height allowed -----------------------------------

		this.maxSizeY  = function (pB, pSize, pSizeBy, pStartSize){

			var newSizeBy        = pSizeBy;
			if(pSize < 1){return  0-pStartSize;}         //-- reduce distance that can be dragged to minimum

//			if(this.getTop() + pSize > pB){              //-- reduce distance that can be dragged
//				newSizeBy        = pB - this.getTop() - this.dragStartHeight;
//				return newSizeBy;
//			}
			return newSizeBy;
		}
		
//--------- run when adjusting sections -------------------

		this.adjustObjectInsideSection  = function (pBottom, pChangeBy){
			var bottom           = parseInt(pBottom);
			if(this.getTop() >= bottom){                                 //-- below section being resized
				this.setTop(this.getTop() + this.borderWidth + pChangeBy);
			}
		}
		
//--------- get the section that this object sits in -------------------
		
		this.getSection  = function (){

			for (var i = 0 ; i < nuSE.length ; i++){
				var s    = nuSE[i];
				if(this.getTop() + this.borderWidth >= s.getTop() && this.getTop() + this.borderWidth < s.getBottom()){	
					return s;                                      //-- section object
				}
				if(this.getTop() + this.borderWidth < nuSE[i+1].getTop()){
					this.setTop(this.getTop() + (nuSE[i+1].getTop() - (this.getTop() + this.borderWidth)));
					return nuSE[i+1];                              //-- section object
				}
			}
			return s;                                              //-- must be last section object
		}

//--------- set time object was last clicked -------------------
		
		this.setLastClicked                = function (e){
			
			if(!e || typeof e == 'undefined'){e=window.event;}
			lastObjectClicked              = this.index;
			this.offsetX                   = e.clientX - this.getLeft();
			this.offsetY                   = e.clientY - this.getTop() - this.borderWidth;
			this.dragStartWidth            = this.getWidth() - (this.borderWidth * 2);
			this.dragStartHeight           = this.getHeight() - (this.borderWidth * 2);
			mouseDownX                     = e.clientX;
			mouseDownY                     = e.clientY;
		}
		
//--------- edit this object -------------------
		
		this.editMe                        = function (){
			this.selected                  = true;
			this.inside.style.outline      = '1px dashed red';
			this.inside.focus();
			this.inside.select();
		}
		
//--------- select this object -------------------
		
		this.selectMe                      = function (){
			this.selected                  = true;
			this.inside.style.outline     = '1px solid red';
		}
		
//--------- unselect this object -------------------
		
		this.unSelectMe                    = function (){
			this.selected                  = false;
			this.inside.style.outlineStyle = 'none';
		}
		
//---------- get left -------------------
		this.getLeft                       = function (){
			return parseInt(this.inside.style.left);
		}
//---------- set left -------------------
		this.setLeft                       = function (pNewLeft){
			this.dragStartX                = parseInt(pNewLeft);
			this.inside.style.left         = this.dragStartX +'px';
		}
//---------- adjusted left -------------------
		this.getAdjustedLeft               = function (){
			return this.getLeft() - adjustLeft();
		}
//---------- get top -------------------
		this.getTop                        = function (){
			return parseInt(this.inside.style.top) - this.borderWidth;
		}
//---------- set top -------------------
		this.setTop                        = function (pTop){
			this.dragStartY                = parseInt(pTop);
			this.inside.style.top         = this.dragStartY + 'px';
		}
//---------- top inside section -------------------
		this.getFromSectionTop          = function (){
			var theS                    = this.getSection();
			return this.getTop() - theS.getTop();
		}
//---------- set width ------------------
		this.setWidth                     = function (pWidth){
			sectionRight                  = nuSE[0].getRight();
			if(sectionRight > parseInt(pWidth) + parseInt(this.inside.style.left) + this.borderWidth){
				this.inside.style.width   = parseInt(pWidth) + 'px';
				return pWidth;
			}else{
				this.inside.style.width   = (sectionRight - (parseInt(this.inside.style.left) - this.borderWidth)) + 'px';
				return parseInt(this.inside.style.width);
			}
		}
//---------- set height ------------------
		this.setHeight                    = function (pHeight){
			this.inside.style.height     = parseInt(pHeight) + 'px';
		}
//---------- set border width ------------------
		this.setBorderWidth                    = function (pWidth){
			this.borderWidth                   = parseInt(pWidth);
			this.inside.style.borderWidth      = this.borderWidth + 'px ' + this.borderWidth + 'px ' + this.borderWidth + 'px ' + this.borderWidth + 'px';
		}
//---------- set border color ------------------
		this.setBorderColor                    = function (pColor){
			this.borderColor                   = pColor;
			this.inside.style.borderColor      = this.borderColor + ' ' + this.borderColor + ' ' + this.borderColor + ' ' + this.borderColor;
		}
//---------- set border Style ------------------
		this.setBorderStyle                    = function (pStyle){
			this.borderStyle                   = pStyle;
			this.inside.style.borderStyle      = this.borderStyle + ' ' + this.borderStyle + ' ' + this.borderStyle + ' ' + this.borderStyle;
		}
//---------- width ------------------
		this.getWidth                     = function (){
			return parseInt(this.inside.style.width) + (this.borderWidth * 2);
		}
//---------- height -------------------
		this.getHeight                    = function (){
			return parseInt(this.inside.style.height) + (this.borderWidth * 2);
		}
//---------- right -------------------
		this.getRight                     = function (){
			return this.getLeft() + this.getWidth();
		}
//---------- bottom -------------------
		this.getBottom                    = function (){
			return this.getTop() + this.getHeight();
		}
		
	}

//==================== end of object class ==============================	
	
	function getObject(pID){   //-- pass either the inside ID or the index number
	
		for(var i = 0 ; i < nuOB.length ; i++){
			if(nuOB[i].id == pID || nuOB[i].index == pID){
				return nuOB[i];
			}
		}
	}


	function getSection(pID){   //-- pass reportIndex or elementid to get section object
	
		for(var i = 0 ; i < nuSE.length ; i++){
			if(nuSE[i].reportIndex == pID || nuSE[i].id == pID){
				return nuSE[i];
			}
		}
	}


	function getSectionsFromSortIndex(pID){   //-- pass sortIndex to get section header and footer object
	
		var HandF    = Array();
		for(var i = 0 ; i < nuSE.length ; i++){
			if(nuSE[i].sortIndex == pID){
				HandF.push(nuSE[i]);
			}
		}
		return HandF;
	}


	function reportLeft(){
		return parseInt(nuSE[0].section.style.left);
	}

	function reportRight(){
		return parseInt(nuSE[0].section.style.left) +  parseInt(nuSE[0].section.style.width);
	}

	function reportBottom(){
		return parseInt(nuSE[nuSE.length-1].section.style.top) +  parseInt(nuSE[nuSE.length-1].section.style.height);
	}


	
	radioButtons[0]                  = 'reportSection';
	radioButtons[1]                  = 'pageSection';
	radioButtons[2]                  = 'radio0';
	radioButtons[3]                  = 'radio1';
	radioButtons[4]                  = 'radio2';
	radioButtons[5]                  = 'radio3';
	radioButtons[6]                  = 'radio4';
	radioButtons[7]                  = 'radio5';
	radioButtons[8]                  = 'radio6';
	radioButtons[9]                  = 'radio7';
	radioButtons[10]                 = 'detailSection';
	
	
	
	reportID[0] = 'Report_Header';
	reportID[1] = 'Page_Header';
	reportID[2] = 'Header_0';
	reportID[3] = 'Header_1';
	reportID[4] = 'Header_2';
	reportID[5] = 'Header_3';
	reportID[6] = 'Header_4';
	reportID[7] = 'Header_5';
	reportID[8] = 'Header_6';
	reportID[9] = 'Header_7';
	reportID[10] = 'Detail';
	reportID[11] = 'Footer_7';
	reportID[12] = 'Footer_6';
	reportID[13] = 'Footer_5';
	reportID[14] = 'Footer_4';
	reportID[15] = 'Footer_3';
	reportID[16] = 'Footer_2';
	reportID[17] = 'Footer_1';
	reportID[18] = 'Footer_0';
	reportID[19] = 'Page_Footer';
	reportID[20] = 'Report_Footer';
	
	reportSection['Report_Header']   = '';
	reportSection['Page_Header']     = '';
	reportSection['Header_0']        = '';
	reportSection['Header_1']        = '';
	reportSection['Header_2']        = '';
	reportSection['Header_3']        = '';
	reportSection['Header_4']        = '';
	reportSection['Header_5']        = '';
	reportSection['Header_6']        = '';
	reportSection['Header_7']        = '';
	reportSection['Detail']          = '';
	reportSection['Footer_7']        = '';
	reportSection['Footer_6']        = '';
	reportSection['Footer_5']        = '';
	reportSection['Footer_4']        = '';
	reportSection['Footer_3']        = '';
	reportSection['Footer_2']        = '';
	reportSection['Footer_1']        = '';
	reportSection['Footer_0']        = '';
	reportSection['Page_Footer']     = '';
	reportSection['Report_Footer']   = '';
	
	reportOrder['Detail']            = 0;
	reportOrder['Report_Header']     = 1;
	reportOrder['Report_Footer']     = 2;
	reportOrder['Page_Header']       = 3;
	reportOrder['Page_Footer']       = 4;
	reportOrder['Header_0']          = 5;
	reportOrder['Footer_0']          = 6;
	reportOrder['Header_1']          = 7;
	reportOrder['Footer_1']          = 8;
	reportOrder['Header_2']          = 9;
	reportOrder['Footer_2']          = 10;
	reportOrder['Header_3']          = 11;
	reportOrder['Footer_3']          = 12;
	reportOrder['Header_4']          = 13;
	reportOrder['Footer_4']          = 14;
	reportOrder['Header_5']          = 15;
	reportOrder['Footer_5']          = 16;
	reportOrder['Header_6']          = 17;
	reportOrder['Footer_6']          = 18;
	reportOrder['Header_7']          = 19;
	reportOrder['Footer_7']          = 20;
	
	sectionNo[0]                     = 'Detail';
	sectionNo[1]                     = 'Report_Header';
	sectionNo[2]                     = 'Report_Footer';
	sectionNo[3]                     = 'Page_Header';
	sectionNo[4]                     = 'Page_Footer';
	sectionNo[5]                     = 'Header_0';
	sectionNo[6]                     = 'Footer_0';
	sectionNo[7]                     = 'Header_1';
	sectionNo[8]                     = 'Footer_1';
	sectionNo[9]                     = 'Header_2';
	sectionNo[10]                    = 'Footer_2';
	sectionNo[11]                    = 'Header_3';
	sectionNo[12]                    = 'Footer_3';
	sectionNo[13]                    = 'Header_4';
	sectionNo[14]                    = 'Footer_4';
	sectionNo[15]                    = 'Header_5';
	sectionNo[16]                    = 'Footer_5';
	sectionNo[17]                    = 'Header_6';
	sectionNo[18]                    = 'Footer_6';
	sectionNo[19]                    = 'Header_7';
	sectionNo[20]                    = 'Footer_7';
	
	objectProperties.push('backgroundColor');
	objectProperties.push('borderStyle');
	objectProperties.push('borderWidth');
	objectProperties.push('borderColor');
	objectProperties.push('cangrow');
	objectProperties.push('color');
	objectProperties.push('fontFamily');
	objectProperties.push('fontSize');
	objectProperties.push('fontWeight');
	objectProperties.push('format');
	objectProperties.push('graph');
	objectProperties.push('height');
	objectProperties.push('id');
	objectProperties.push('left');
	objectProperties.push('textAlign');
	objectProperties.push('top');
	objectProperties.push('type');
	objectProperties.push('value');
	objectProperties.push('width');

	controlArray['100']       = 'Label';
	controlArray['109']       = 'Field';
	controlArray['103']       = 'Graph';
	controlArray['118']       = 'PageBreak',
	controlArray['Label']     = 'Label';
	controlArray['Field']     = 'Field';
	controlArray['Graph']     = 'Graph';
	controlArray['PageBreak'] = 'PageBreak';



function objectSection(pObjectTop){

	var oTop     = parseInt(pObjectTop);

	for (var i = 0 ; i < nuSE.length ; i++){
		var s    = nuSE[i];
		if(oTop >= s.getTop() && oTop < s.getBottom()){	
			return s.id;                                      //-- section object
		}
	}
	return s.id;                                              //-- must be last section object
}



function buildClass(){

	if(!checkBeforeSave()){
		return;
	}
	var r = '\n';
	var s = '\n';
	var c = '\n';
	var g = '\n';
	
	r =     "class Reporting{\n\n";
	r = r + "   var $nuBuilder          = '1';\n";
	r = r + "   var $Controls           = array();\n";
	r = r + "   var $Sections           = array();\n";
	r = r + "   var $Groups             = array();\n";
	r = r + "   var $Version            = '3';\n";
	r = r + "   var $Encode             = '1';\n";
	r = r + "   var $Width              = '" + parseInt(document.getElementById('rptWidthProperty').value) + "';\n";
	r = r + "   var $Height             = '" + parseInt(document.getElementById('rptHeightProperty').value) + "';\n";
	r = r + "   var $PaperType          = '" + document.getElementById('rptPaperTypeInput').value + "';\n";
	r = r + "   var $Orientation        = '" + document.getElementById('rptOrientationInput').value + "';\n\n";
	r = r + "\n   function Reporting(){";
	
	var theName          = '';
	var defaultSections  = Array();
	var theSortField     = '';
	var info             = '';
	var HorF             = '';
	
	defaultSections[0]   = 'Detail';
	defaultSections[1]   = 'Report_Header';
	defaultSections[2]   = 'Report_Footer';
	defaultSections[3]   = 'Page_Header';
	defaultSections[4]   = 'Page_Footer';

	for(sF = 0 ; sF < 8 ; sF++){  //====== Groups ====================
		
		theSortField     = document.getElementById('sortField' + sF).value;
		theSortOrder     = document.getElementById('sort0'     + sF).value;

		if(theSortField != ''){
			g            = g + "      $this->Groups[" + sF + "]->Field                 = '" + theSortField + "';\n";
			g            = g + "      $this->Groups[" + sF + "]->SortOrder             = '" + theSortOrder + "';\n";
		}
	}



	for(sNo = 0 ; sNo < 21 ; sNo++){  //====== Sections ====================

		var theS     = nuSE[sNo];
		var Index    = theS.reportIndex;
		if(theS.sectionName != ''){
			s            = s + "      $this->Sections[" + Index + "]->Name                = '"   + theS.sectionName         + "';\n";
			s            = s + "      $this->Sections[" + Index + "]->ControlType         = '"   + Index                    + "';\n";
			s            = s + "      $this->Sections[" + Index + "]->Tag                 = '"   + Index                    + "';\n";
			s            = s + "      $this->Sections[" + Index + "]->Height              = '"   + theS.getHeight()         + "';\n";
			s            = s + "      $this->Sections[" + Index + "]->BackColor           = '"   + theS.getColor()          + "';\n";
			s            = s + "      $this->Sections[" + Index + "]->SectionNumber       = '"   + Index                    + "';\n\n";
		}
	
	}

	for(j = 0; j < nuOB.length; j++){  //====== Objects ====================

		var OB           = nuOB[j];
		var OBSE         = OB.getSection()
		var BW           = parseInt(OB.borderWidth);   //-- border width
		var IN           = OB.inside;
//		fixValue         = OB.inside.value.replace("'","");
		fixValue         = OB.inside.value.replace(/'/g,"&#39;");
		fixValue         = Base64.encode(fixValue);
		var theFormat    = OB.Format;
		var theGraph     = OB.Graph.substr(6);

		if(OB.ControlType == 'Graph'){
			theFormat    =  OB.Graph.substr(0,5);;
			theGraph     =  OB.Graph.substr(6);;
		}

		c       = c + "\n";
		c       = c + "      $this->Controls[" + j + "]->Name                = '"   + OB.id                          + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Section             = '"   + OBSE.reportIndex               + "';\n";
		c       = c + "      $this->Controls[" + j + "]->ControlType         = '"   + OB.ControlType                 + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Tag                 = '"   + OB.ControlType                 + "';\n";
		c       = c + "      $this->Controls[" + j + "]->ControlSource       = '"   + fixValue                       + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Caption             = '"   + fixValue                       + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Value               = '"   + fixValue                       + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Top                 = '"   + (OB.getFromSectionTop() + BW)  + "px';\n";
		c       = c + "      $this->Controls[" + j + "]->Left                = '"   + OB.getAdjustedLeft()           + "px';\n";
		c       = c + "      $this->Controls[" + j + "]->Width               = '"   + (OB.getWidth()  - (BW * 2))    + "px';\n";
		if(OB.ControlType == 'PageBreak'){
			c   = c + "      $this->Controls[" + j + "]->Height              = '"   + 4                              + "px';\n";
		}else{
			c   = c + "      $this->Controls[" + j + "]->Height              = '"   + (OB.getHeight() - (BW * 2))    + "px';\n";
		}
		c       = c + "      $this->Controls[" + j + "]->ForeColor           = '"   + formatColor(IN.style.color)    + "';\n";
		c       = c + "      $this->Controls[" + j + "]->FontSize            = '"   + parseInt(IN.style.fontSize)    + "px';\n";
		c       = c + "      $this->Controls[" + j + "]->FontWeight          = '"   + IN.style.fontWeight            + "';\n";
		c       = c + "      $this->Controls[" + j + "]->FontName            = '"   + IN.style.fontFamily            + "';\n";
		c       = c + "      $this->Controls[" + j + "]->BackColor           = '"   + IN.style.backgroundColor       + "';\n";
		c       = c + "      $this->Controls[" + j + "]->BorderWidth         = '"   + parseInt(OB.borderWidth)       + "px';\n";
		c       = c + "      $this->Controls[" + j + "]->BorderColor         = '"   + OB.borderColor                 + "';\n"; 
		c       = c + "      $this->Controls[" + j + "]->BorderStyle         = '"   + OB.borderStyle                 + "';\n"; 
		c       = c + "      $this->Controls[" + j + "]->Graph               = '"   + theGraph                       + "';\n";
		c       = c + "      $this->Controls[" + j + "]->CanGrow             = '"   + OB.CanGrow                     + "';\n";
		c       = c + "      $this->Controls[" + j + "]->TextAlign           = '"   + IN.style.textAlign             + "';\n";
		c       = c + "      $this->Controls[" + j + "]->Format              = '"   + theFormat                      + "';\n";
	}
	
	s = r + c + s + g + "\n   }\n}";
	
	document.getElementById('classcode').value = s;
    copyClass();
}


function copyClass(){
	window.opener.document.getElementById('sat_report_display_code').value=document.getElementById('classcode').value;
	window.opener.document.getElementById('beenedited').value = '1';
	window.opener.focus();
	alert('Copied Successfully to Activity Table');
	self.close();
}


function checkBeforeSave(){
	//check the report height
	if(isNaN(parseInt(document.getElementById('rptWidthProperty').value,10))){
		alert("Please input a width for the report");
		return false;
	}
	//check the report width
	if(isNaN(parseInt(document.getElementById('rptHeightProperty').value,10))){
		alert("Please input a height for the report");
		return false;
	}
	return true;
	//check the report page height is high enough to have all the appropriate sections present on a page
	//gather data
	var reportHeight = parseInt(document.getElementById('rptHeightProperty').value,10);
	var defaultHeaders = 0;
	var defaultFooters = 0;
	var customHeaders = 0;
	var customFooters = 0;
	var detailSection = 0;
	for(i = 0 ; i < sectionNo.length ; i++){
		if(getSectionActive(sectionNo[i]) > 0){
			if(sectionNo[i] == 'Report_Header' || sectionNo[i] == 'Page_Header'){
				defaultHeaders += parseInt(getStyle(sectionNo[i],'height'),10);
			}else if(sectionNo[i] == 'Report_Footer' || sectionNo[i] == 'Page_Footer'){
				defaultFooters += parseInt(getStyle(sectionNo[i],'height'),10);
			}else if(sectionNo[i] == 'Detail'){
				detailSection += parseInt(getStyle(sectionNo[i],'height'),10);
			}else if(sectionNo[i].indexOf('_Header') >= sectionNo[i].length - 7){
				customHeaders += parseInt(getStyle(sectionNo[i],'height'),10);
			}else if(sectionNo[i].indexOf('_Footer') >= sectionNo[i].length - 7){
				customFooters += parseInt(getStyle(sectionNo[i],'height'),10);
			}
		}
	}
	
	//check values
	var sum;
	
	//default headers + detail
	sum = defaultHeaders + detailSection;
	if( sum > reportHeight ){
		alert("Report Header + Page Header + Detail heights are larger than the page height. Page Height: " + reportHeight + " vs Sum Height: " + sum);
		return false;
	}
	//default footers + detail
	sum = defaultFooters + detailSection;
	if( sum > reportHeight ){
		alert("Report Footer + Page Footer + Detail heights are larger than the page height. Page Height: " + reportHeight + " vs Sum Height: " + sum);
		return false;
	}
	//default headers + detail + custom headers/footers
	sum = defaultHeaders + detailSection + customHeaders + customFooters;
	if( sum > reportHeight ){
		alert("Report Header + Page Header + Detail + Custom Section heights are larger than the page height. Page Height: " + reportHeight + " vs Sum Height: " + sum);
		return false;
	}
	//default footers + detail + custom headers/footers
	sum = defaultFooters + detailSection + customHeaders + customFooters;
	if( sum > reportHeight ){
		alert("Report Footer + Page Footer + Detail + Custom Section heights are larger than the page height. Page Height: " + reportHeight + " vs Sum Height: " + sum);
		return false;
	}
	return true;
}


//checking sections if they are active or not
function getSectionActive(id){
	if(sectionActive[id] == undefined){
		setSectionActive(id,0);
		return 0;
	}
	return sectionActive[id];
}


function formatColor(colorstr){   //-- reformat Hex Or RGB To Hex
	colorstr = colorstr.toLowerCase();
	if(colorstr.substr(0,3) == 'rgb'){
		colorstr = colorstr.replace(/rgb|\(|\)/g,'');
		var split = colorstr.split(',');
		var ra = parseInt(split[0],10);
		var ga = parseInt(split[1],10);
		var ba = parseInt(split[2],10);
		var r = ra.toString(16);
		var g = ga.toString(16);
		var b = ba.toString(16);
		if (r.length == 1) r = '0' + r;
        if (g.length == 1) g = '0' + g;
        if (b.length == 1) b = '0' + b;
		return '#' + r + g + b;
	}
	return colorstr;
}


function isEven(pValue){
	return pValue%2 == 0;  //-- its even
}

	
<?php

/*
** File:           nureportbuilder.php
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

$dir                             = $_GET['dir'];
$reportID                        = $_GET['r'];
$convert						 = isset($_GET['conv']) ? $_GET['conv'] : '0';

if (strpos($dir,"..") !== false)
	die;

require_once("../$dir/database.php");
require_once('common.php');



$t                               = nuRunQuery("SELECT sat_report_display_code FROM zzsys_activity WHERE zzsys_activity_id = '$reportID'");
$r                               = db_fetch_row($t);
$blankReport                     = true;
if($r[0]==''){
	eval('class Reporting{var $nuBuilder = "1";var $Controls = array();var $Sections = array();var $Groups = array();var $Width = "900px";var $Height = "1000px";function Reporting(){}}');
}else{
	eval($r[0]);
	$blankReport                 = false;
}





//-- arrays for html select objects

print "\nvar theControl = new Array()\n";
print "theControl[0] = 'Field'\n";
print "theControl[1] = 'Label'\n";
print "theControl[2] = 'Graph'\n";
print "theControl[3] = 'PageBreak'\n";

print "\nvar theWeight = new Array()\n";
print "theWeight[0] = 'normal'\n";
print "theWeight[1] = 'bold'\n";
print "theWeight[2] = 'lighter'\n";

print "\nvar theFamily = new Array()\n";
print "theFamily[0] = 'Arial'\n";
print "theFamily[1] = 'Courier'\n";
print "theFamily[2] = 'Georgia'\n";
print "theFamily[3] = 'Impact'\n";
print "theFamily[4] = 'Tahoma'\n";
print "theFamily[5] = 'Times'\n";
print "theFamily[6] = 'Verdana'\n";
print "theFamily[7] = 'Symbol'\n";
//print "theFamily[8] = 'Webdings'\n";
//print "theFamily[9] = 'Wingdings'\n";
$nuFontT = nuRunQuery("SELECT sli_option FROM zzsys_list WHERE sli_name = 'reportFont'");
$fontNo = 8;
while($nuFontR = db_fetch_object($nuFontT))
{
	print "theFamily[$fontNo] = '$nuFontR->sli_option'\n";
	$fontNo++;
}

//--add graphs to graph list
	print "\nvar theGraph = new Array()\n";
	$tg         = nuRunQuery("SELECT sag_graph_name FROM zzsys_activity_graph WHERE sag_zzsys_activity_id = '$reportID'");
	$tcount     = 1;
	print "theGraph[0] = ''\n";
	while($rg   = db_fetch_row($tg)){
		print "theGraph[$tcount] = 'graph-$rg[0]'\n";
		$tcount = $tcount + 1;
	}
	
//--add images to graph list
	$tg         = nuRunQuery("SELECT sim_code FROM zzsys_image ");
	while($rg   = db_fetch_row($tg)){
		print "theGraph[$tcount] = 'image-$rg[0]'\n";
		$tcount = $tcount + 1;
	}





print "\nvar theAlign = new Array()\n";
print "theAlign[0] = 'left'\n";
print "theAlign[1] = 'right'\n";
print "theAlign[2] = 'center'\n";

print "\nvar theStyle = new Array()\n";
print "theStyle[0] = 'solid'\n";
print "theStyle[1] = 'dashed'\n";
print "theStyle[2] = 'dotted'\n";
print "theStyle[3] = 'double'\n";

print "\nvar canGrow = new Array()\n";
//print "canGrow[0] = 'No'\n";
//print "canGrow[1] = 'Yes'\n";
print "canGrow[0] = 'False'\n";
print "canGrow[1] = 'True'\n";

$textFormat = textFormatsArray();
print "\nvar theFormat = new Array()\n";
for($i = 0 ; $i < count($textFormat) ; $i++)
{
	print   "theFormat[$i] = '" . $textFormat[$i]->sample. "'\n";
}
print "theFormat[32] = ''\n";


//-- arrays from report class object

$rClass             = new Reporting();
print "\nfunction buildReport(){  //-- run on onload event\n\n";	

print "   createTempInput();\n";	

//-- get starting width of report

if($rClass->Width == ''){
	$maxWidth = 1000;
}else{
	$maxWidth = losePX($rClass->Width);
	
}




//-- build report dimensions

print "\n\n    document.getElementById('rptWidthProperty').value          = '$maxWidth';\n";

if($rClass->Height == ''){
	print "    document.getElementById('rptHeightProperty').value         = '900px';\n";
}else{
	print "    document.getElementById('rptHeightProperty').value         = '$rClass->Height';\n";
}
if($rClass->PaperType == ''){
	print "    document.getElementById('rptPaperTypeInput').value      = 'A4';\n";
}else{
	print "    document.getElementById('rptPaperTypeInput').value      = '$rClass->PaperType';\n";
}
if($rClass->Orientation == ''){
	print "    document.getElementById('rptOrientationInput').value    = 'P';\n";
}else{
	print "    document.getElementById('rptOrientationInput').value    = '$rClass->Orientation';\n";
}



//-- build section objects into arrays

print "   nuSE.push(new nuSection(1));\n";	
print "   nuSE.push(new nuSection(3));\n";	
print "   nuSE.push(new nuSection(5));\n";	
print "   nuSE.push(new nuSection(7));\n";	
print "   nuSE.push(new nuSection(9));\n";	
print "   nuSE.push(new nuSection(11));\n";	
print "   nuSE.push(new nuSection(13));\n";	
print "   nuSE.push(new nuSection(15));\n";	
print "   nuSE.push(new nuSection(17));\n";	
print "   nuSE.push(new nuSection(19));\n";	
print "   nuSE.push(new nuSection(0));\n";	
print "   nuSE.push(new nuSection(20));\n";	
print "   nuSE.push(new nuSection(18));\n";	
print "   nuSE.push(new nuSection(16));\n";	
print "   nuSE.push(new nuSection(14));\n";	
print "   nuSE.push(new nuSection(12));\n";	
print "   nuSE.push(new nuSection(10));\n";	
print "   nuSE.push(new nuSection(8));\n";	
print "   nuSE.push(new nuSection(6));\n";	
print "   nuSE.push(new nuSection(4));\n";	
print "   nuSE.push(new nuSection(2));\n";	

//-- build groups

	
for($i = 0 ; $i < count($rClass->Groups) ; $i++){

	$rGroup       = $rClass->Groups[$i];
	print "    document.getElementById('sortField$i').value  = '$rGroup->Field';\n";	
	print "    document.getElementById('sort0$i').value      = '$rGroup->SortOrder';\n";	
	print "    applySortToSection($i, '$rGroup->Field');\n";	

}


print "\n\n//-- set section properties  \n\n";	
print "    var rSE = Object();\n\n";	

if($blankReport){
	print "\n\n//-- adjust section objects  \n\n";	
	print "    for(var i = 0 ; i < nuSE.length ; i++){\n";	
	print "        nuSE[i].setSectionProperties(document.getElementById(nuSE[i].id).style.height, '#ebebeb', document.getElementById(nuSE[i].id).innerHTML);\n";	
	print "    }\n\n";	
}else{
	for($i = 0 ; $i < count($rClass->Sections) ; $i++){

		$rSection    = $rClass->Sections[$i];
		print "    rSE  = getSection($rSection->SectionNumber);\n";	
		print "    rSE.setSectionProperties('$rSection->Height','$rSection->BackColor','$rSection->Name');\n";	

	}
}



print "\n\n//-- adjust section objects  \n\n";	
print "    for(var i = 0 ; i < nuSE.length ; i++){\n";	
print "       nuSE[i].setSectionPosition();\n";
print "    }\n\n";	


$adjustLeftBy  = 22;

//-- build controls
for($i = 0 ; $i < count($rClass->Controls) ; $i++){

	$rControl      = $rClass->Controls[$i];
	$newLeft       = $rControl->Left + $adjustLeftBy;
	$string        = "    buildObject('$rControl->Name','$rControl->Section','$rControl->Top','$newLeft','$rControl->Width','$rControl->Height', ";
	$string       .= "'$rControl->BackColor', '$rControl->BorderWidth', '$rControl->BorderColor', ";
	$string       .= "'$rControl->ForeColor', '$rControl->FontName', '$rControl->FontSize', ";
	$string       .= "'$rControl->FontWeight', '$rControl->TextAlign', controlArray['$rControl->ControlType'], ";
	if($rClass->Encode == '1'){  //-- added by sc 2012-02-08 to stop weird characters breaking the report builder
		$string   .= "'$rControl->Graph', '$rControl->CanGrow', '$rControl->Format', Base64.decode('$rControl->Value'), ";
	}else{
		$string   .= "'$rControl->Graph', '$rControl->CanGrow', '$rControl->Format', '$rControl->Value', ";
	}
	$string       .= "'$rControl->BorderStyle', '$rControl->BorderWidth',  '$rControl->BorderColor') ;\n";
	print $string;	
	
	$maxWidth = max($maxWidth, losePX($rControl->Width) + $newLeft);
}


if($blankReport){
	print "    adjustSections('Page_Header', 50);\n";	
	print "    adjustSections('Detail', 80);\n";	
	print "    adjustSections('Page_Footer', 50);\n";	
	print "    adjustSections('report_Footer', 0);\n";	
}else{
	print "    adjustSections('Detail', 0);\n";	
}

print "}\n\n";	

print "    function adjustLeft(){\n        return $adjustLeftBy;\n    }\n\n";
?>	
	
	function keyDown(e){
	
		if(!e){e=window.event;}
		
		if(e.keyCode == 27){									//-- Esc key
			objDialogOpen = false;
			hideAllDialogs();
		}
		
		if(e.keyCode == 16){  									//-- shift key
			shiftKey  = true;
		}
		if(e.keyCode == 17){ 									//-- control key
			ctrlKey   = true;
		}
		if (e.keyCode == 46){									//-- delete key (shortcut for delete object)
			deleteObjects();
		}
/*---not working
		if (ctrlKey && e.keyCode == 90){	                    //-- ctrl-z (undo)
			
			if(nuHI.length == 0){return;}                       //-- nothing to reverse
			for(var i = 0 ; i < nuOB.length ; i ++){            //-- unselect everything
				nuOB[i].unSelectMe();
			}

			var lastSave                   = nuHI.pop();        //-- last historical save
			lastSave.undoPosition();
			
			while(nuHI[nuHI.length-1].Time == lastSave.Time){
				var nextSave               = nuHI.pop();
				nextSave.undoPosition();
				if(nuHI.length == 0){return;}
			}
		}
*/
		if(!aDialogWasClickedLast){  //-- dont move with arrow keys

			if (e.keyCode == 37 && selectMode=='object'){		//-- left arrow key; move selected objects 1 pixel left
				addUndoHistory();
				moveSelected(e, -1, 0)
			}
			if (e.keyCode == 39 && selectMode=='object'){		//-- right arrow key; move selected objects 1 pixel right
				addUndoHistory();
				moveSelected(e, 1, 0)
			}
			if (e.keyCode == 38 && selectMode=='object'){		//-- up arrow key; move selected objects 1 pixel up
				addUndoHistory();
				moveSelected(e, 0, -1)
			}
			if (e.keyCode == 40 && selectMode=='object'){	    //-- down arrow key; move selected objects 1 pixel down
				addUndoHistory();
				moveSelected(e, 0, 1)
				for(var i = 0 ; i < nuOB.length ; i++){
					if(nuOB[i].selected){
						var s                  = nuOB[i].getSection();
						nuOB[i].sectionID      = s.id;
						squeezeInObject(nuOB[i]);                          //-- resize section if need be
					}
				}
				
			}
		}
	}

	
	
	function markObjectAsSelected(pID){
	
		selectedObjects.push(pID);
		var currentOB                = getObject(pID);
		currentOB.selectMe();
	
	}
	
	
	function keyUp(e){
		if(!e){e=window.event;}
		if(e.keyCode == 16){  //-- shift key
			shiftKey  = false;
		}
		if(!e){e=window.event;}
		if(e.keyCode == 17){  //-- control key
			ctrlKey   = false;
		}
	}

	function getAbsoluteLeft(pObject){   //-- absolute left position of object as if it wasn't in a div

		var p = pObject.parentNode;
		return parseInt(p.style.left) +  parseInt(p.parentNode.style.left);

	}
   
   
	function getAbsoluteTop(pObject){    //-- absolute top position of object as if it wasn't in a div

		var p = pObject.parentNode;
		return parseInt(p.style.top) +  parseInt(p.parentNode.style.top);

	}
   

	function getSelectedObjects(){  //-- return an array of selected objects
	
		var selectedObjects = Array();
		for(var i = 0 ; i < nuOB.length ; i++){
			if(nuOB[i].selected){
				selectedObjects.push(nuOB[i]);
			}
		}
		return selectedObjects;
	}

	function noSelections(){  //-- return true if there is nothing selected
		
		if(!ctrlKey){
			return true;
		}
		
		for(var i = 0 ; i < nuOB.length ; i++){
			if(nuOB[i].selected){
				return false;
			}
		}
		return true;
	}

	function adjustSections(pSection, pChangeHeightBy){

		var newTop                      = reportTop;   //-- start first section here
		var sectionDetails              = Array();
		var reAdjustObject              = 0;
		var adjustedSectionHeight       = 0;
		var sBottom                     = 0;
		var newChangeHeightBy           = pChangeHeightBy;
		var pushDown                    = false;
		var sectionWidth                = parseInt(document.getElementById('rptWidthProperty').value) + 'px';

		//-- adjust sections
		for(var i = 0; i < nuSE.length; i++){

			s                                 = nuSE[i];
			s.setWidth(sectionWidth);
			if(s.id == pSection){
				pushDown                      = true;
				sTop                          = s.getTop();
				sBottom                       = s.getBottom();
				var minimumHeight             = smallestSectionHeight(s);
				if (s.getHeight() + pChangeHeightBy == 0){
					if(s.getHeight() != 0){             //-- if section was visible
						reAdjustObject        = -2;     //-- allow for disappearing margin on section
					}
					if(minimumHeight == 0){             //-- there is no minimum height
						s.setHeight(0);
					}else{
						newChangeHeightBy     = pChangeHeightBy - minimumHeight;                //-- change would make section too small for objects
						s.setHeight(minimumHeight);
					}
				}else{
					if(s.getHeight() == 0){            //-- if section was not visible
						reAdjustObject        = 2;      //-- allow for appearing margin on section
					}
					if(minimumHeight == 0){             //-- there is no minimum height
						s.setHeight(s.getHeight() + pChangeHeightBy);
					}else{
						if(minimumHeight > s.getHeight() + pChangeHeightBy){          //-- too small for objects
							newChangeHeightBy = minimumHeight - s.getHeight();
						}
						s.setHeight(Math.max(minimumHeight, s.getHeight() + pChangeHeightBy));
					}
				}
				adjustedSectionHeight         = s.getHeight();
			}else{
				if(pushDown){
					s.setSectionPosition();
				}
			}
			if(s.getHeight() != 0){
				newTop                        = s.getBottom() + 2;
			}
		}
		if(sBottom != 0){  //-- might need adjusting
			for(i = 0 ; i < nuOB.length ; i++){
				nuOB[i].adjustObjectInsideSection(sBottom, newChangeHeightBy + reAdjustObject);		
			}
		}

		return adjustedSectionHeight;
	}
	
	
	function smallestSectionHeight(pSection){

		var oBottom             = 0;
		var Bottom              = 0;

		for(var i = 0; i < nuOB.length; i++){
			var s               = nuOB[i].getSection();
			if(s.id == pSection.id){
//				oBottom         = (nuOB[i].getTop() + (nuOB[i].getHeight()-nuOB[i].borderWidth)) - pSection.getTop();
				oBottom         = (nuOB[i].getTop() + nuOB[i].getHeight()) - pSection.getTop();
				Bottom          = Math.max(Bottom, oBottom);
			}
		}

		return Bottom;

	}


	function newObjectID(){

		for(var i = 100 ; i < 1000 ; i++){
			var s = String(i);
			if(!document.getElementById('object'+s)){ //--there is no object with this ID
				return 'object'+s;
			}
		}

	}
   
	function isSelected(pID){                         //-- see if ID is in array of selected objects

		var o                 = getObject(pID);
		if(!o){return false;}
		var nuSEL             = getSelectedObjects();
		for(var i = 0 ; i < nuSEL.length ; i++){
			if(nuSEL[i].id == o.id){return true;}
		}
		return false;	

	}

	function unselectObjects(section){

		if(ctrlKey){
			for(var i = 0 ; i < nuOB.length ; i++){
				if(section.id == nuOB[i].sectionID){
					nuOB[i].selectMe();
				}
			}
		}else{
			for(var i = 0 ; i < nuOB.length ; i++){
				 nuOB[i].unSelectMe();
			}
		}

		selectedObjects = Array();  //--set selected objects to 0
		newArray        = Array();
		mouseDownWidth = Array();
		mouseDownHeight = Array();
		selectedSection = section;
		selectMode = 'object';
		doNotPasteObjects = false;

		if (objDialogOpen) openDialog();
	}
   
	function selectObject(pID, pGroup){  			//--select object (actually selects the div around the object)

		var thisOB      = getObject(pID)

		if(ctrlKey){
			if(thisOB.selected && !pressedCtrlA){ //-- toggle it off and keep other selected objects selected
				if(arguments.length == 1){        //-- not a group selection
					thisOB.unSelectMe();
				}
			}else{
				thisOB.selectMe();
				for(var i = 0 ; i < nuOB.length ; i++){	  //-- this loop ensures if you select multiple objects while in text mode, borders are reapplied correctly
					if(nuOB[i].selected){
						nuOB[i].selectMe();
					}
				}
			}
		}else{		//-- Otherwise select only this object, unless it's already in the selection (to allow for group moving/resizing)

			if(!thisOB.selected){
				for(var i = 0 ; i < nuOB.length ; i++){	
					nuOB[i].unSelectMe();
				}
				thisOB.selectMe();
			}
		}

		if (objDialogOpen && !pressedCtrlA){
			openDialog();
		}
	}
	
	function enterTextMode(pThis){					//-- Go into text select mode on double click.

		objDialogOpen = true;
		openDialog();

		var selObjects = getSelectedObjects();
		if (selObjects.length != 1) return;	//-- Can't go into text mode when multiple objects selected
		
		selectMode = 'text'; 
		selObjects[0].editMe();
	}
	
	function addUndoHistory(){
/*  not working
		var d                            = new Date();
		var t                            = d.getTime();
		var o                            = Object();
		for(var i = 0 ; i < nuOB.length ; i ++){   //-- selected objects
			o                            = nuOB[i];
			nuHI.push(new nuHistory(true, t, o.id, o.getLeft(), o.getTop(), o.getWidth() - (o.borderWidth * 2), o.getHeight() - (o.borderWidth * 2)));
		}
		for(var i = 0 ; i < nuSE.length ; i ++){    //-- sections
			o                            = nuSE[i];
			nuHI.push(new nuHistory(false, t, o.id, 0, o.getTop(), o.getWidth() - (o.borderWidth * 2), o.getHeight() - (o.borderWidth * 2)));
		}
		if(nuHI.length > (nuOB.length + nuSE.length) * 10){
			removeOldestUndoHistory(nuHI[0].Time);
		}
*/
	}
	
	function removeOldestUndoHistory(pTime){

		while(nuHI[0].Time == pTime){
			nuHI.shift();
		}

	}


	function nuthin(){}
	
	function mouseDn(e, pThis){
		pThis.style.cursor               = 'move';
		//-- Don't allow regular movement/behaviour of the selected objects when in text mode.
		if (selectMode == 'text' && isSelected(pThis.id)) return;	

		mouseIsDown                      = true;
		if(!e){e=window.event;}
		
		theClass                         = pThis.className;
		theID                            = pThis.id;
		aDialogWasClickedLast            = (theClass == 'nuDialog');
		if (theClass == 'nuObject'){		//-- Single click on an object selects it and goes into object mode.
		
			mouseDownWaitComplete = false;
			setTimeout(function(){mouseDownWaitComplete = true},300);

			addUndoHistory();

			var thisOB                   = getObject(theID);
			pThis.style.cursor           = 'move';
			var pThis                    = document.getElementById(theID);
			
			setTimeout(function(){selectObject(pThis.id)},10);
			selectedSection              = document.getElementById(objectSection(pThis.style.top));
			selectMode                   = 'object';
			thisOB.setLastClicked(e);

			setTimeout(function(){document.getElementById('tempID').focus()},10);	// Unfocus to remove the blinking text cursor from the field.
		}
		
		if (theClass == 'nuDialog'){
			var nuSEL = getSelectedObjects();
			if(nuSEL.length == 1){
				theOB = getObject(nuSEL[0].id);
				theOB.selectMe();  //-- remove object from edit mode
			}
			doNotPasteObjects            = true;
		}else{
			doNotPasteObjects            = false;
		}
		
		offsetX                          = e.clientX - parseInt(pThis.style.left);
		offsetY                          = e.clientY - parseInt(pThis.style.top);
	}
	
	function mouseUp(e,pThis){
		
		theID                 = '';
		theClass              = '';
		shiftKey              = false;
		mouseIsDown			  = false;
		startedMoving 		  = false;
		var isLineDown        = document.getElementById('nuLineDown');
		var isLineAcross      = document.getElementById('nuLineAcross');
		if(isLineDown){
			var p             = document.getElementById('nuLineDown').parentNode;
			var r             = document.getElementById('nuLineDown');
			selectBoxedObjects(r.id);
		}
		if(isLineAcross){
			var p             = document.getElementById('nuLineAcross').parentNode;
			var r             = document.getElementById('nuLineAcross');
			selectBoxedObjects(r.id);
		}
		if(!isLineAcross && !isLineDown){  //-- not selecting a group of objects at once
			if (pThis != undefined){
				theClass = pThis.className;
			}
			if (theClass == 'nuClose' || theClass == '' || theClass == 'nuDialog'){
				return;
			}
			
			//-- Only open the object properties dialog if a nuObject has been clicked on.
			if (objDialogOpen && theClass == 'nuObject'){
				openDialog();
			}
			if (theClass == 'nuObject'){
				pThis.style.cursor = 'text';
			}
			if (theClass == 'nuDialog'){
				var nuSEL                 = getSelectedObjects();  //-- returns an array of selected objects
				if(nuSEL.length == 1){
					nuSEL[0].selectMe();  //-- take object out of edit mode
				}
			}



			for(var i = 0 ; i < nuOB.length ; i++){
				var s                    = nuOB[i].getSection();
				nuOB[i].sectionID        = s.id;
				squeezeInObject(nuOB[i]);                          //-- resize section if need be
				nuOB[i].dragStartWidth   = nuOB[i].getWidth() - (nuOB[i].borderWidth * 2);     //-- reset start size
				nuOB[i].dragStartHeight  = nuOB[i].getHeight() - (nuOB[i].borderWidth * 2);    //-- reset start size
			}
		}

	}
	
	function mouseOver(pThis){
		pThis.style.color                 = 'red';
	}
	
	function mouseOverObject(pThis)
	{
		pThis.style.cursor = 'pointer';
	}

	function mouseOut(pThis){
		if (pThis.id == 'OP_delete') {	pThis.style.color = 'yellow';	return;	}
		pThis.style.color                 = 'white';
	}
	function toolOver(pThis){
		pThis.style.color                 = 'red';
		pThis.style.backgroundColor       = '#CBCAD0';
	}

	function toolOut(pThis){
		pThis.style.color                 = 'black';
		pThis.style.backgroundColor       = '#EBEBEB';
	}
	
	function closeDialog(pThis){
		objDialogOpen = false;
		pThis.parentNode.style.visibility = "hidden";
	}

	function mouseMove(e){
		
		if(!e){e=window.event;}
		if(theID == ''){return;}
		
		var theO                          = document.getElementById(theID);
		var theD                          = document.getElementById('div'+theID);


		if(theClass == 'Top_Margin'){

			if(offsetX > e.clientX){
				theO.style.left           = e.clientX +'px';
				theO.style.width          = offsetX - e.clientX +'px';
			}else{
				theO.style.left           = offsetX +'px';
				theO.style.width          = e.clientX - offsetX +'px';
			}
			
		}
		if(theClass == 'Left_Margin'){

			if(offsetY > e.clientY){
				theO.style.top            = e.clientY +'px';
				theO.style.height         = offsetY - e.clientY +'px';
			}else{
				theO.style.top            = offsetY +'px';
				theO.style.height         = e.clientY - offsetY +'px';
			}
			
		}
		if(theClass == 'nuDialog'){
		
			theO.style.left               = e.clientX - offsetX + 'px';
			theO.style.top                = e.clientY - offsetY + 'px';
			if(parseInt(theO.style.left)  < 0){theO.style.left = '0px';}   //-- don't lose object off the screen
			if(parseInt(theO.style.top)   < 0){theO.style.top  = '0px';}   //-- don't lose object off the screen

		}

		if(theClass == 'nuObject' && !ctrlKey){
			if (!mouseDownWaitComplete){return;}
			moveSelected(e);
		}
	}


	function moveSelected(e, pX, pY){
		
		if(!e){e=window.event;}
		if (!startedMoving && objDialogOpen){
			openDialog();
		}
		startedMoving = true;
		
		if (navigator.userAgent.indexOf("Chrome") != -1){	//Under chrome need to unfocus again once moving has begun, for some reason.
			setTimeout(function(){document.getElementById('tempID').focus()},10);
		}
		var l                         = nuSE[0].getLeft();
		var t                         = nuSE[0].getTop();
		var r                         = nuSE[0].getRight();
		var b                         = nuSE[20].getBottom();
		var lastO                     = nuOB[lastObjectClicked];
		if(arguments.length > 1){  //-- keystrokes
			var moveXBy                   = pX;
			var moveYBy                   = pY;
			var sizeXBy                   = pX;
			var sizeYBy                   = pY;
		}else{
			var moveXBy                   = e.clientX - lastO.dragStartX;
			var moveYBy                   = e.clientY - lastO.dragStartY;
			var sizeXBy                   = e.clientX - mouseDownX;
			var sizeYBy                   = e.clientY - mouseDownY;
		}
		var nuSEL                     = getSelectedObjects();  //-- returns an array of selected objects
		var moveX                     = 0;
		var moveY                     = 0;
		var SectionID                 = '';
		multipleSections              = false;

		if(shiftKey){                                                      //-- resize selected objects
			for(var i = 0 ; i < nuSEL.length ; i++){
				if(SectionID != '' && SectionID != nuSEL[i].sectionID){multipleSections = true;}
				SectionID             = nuSEL[i].sectionID;
				sizeY                 = nuSEL[i].dragStartHeight + sizeYBy;
				sizeYBy               = nuSEL[i].maxSizeY(b, sizeY, sizeYBy, nuSEL[i].dragStartHeight);

				sizeX                 = nuSEL[i].dragStartWidth + sizeXBy;
				sizeXBy               = nuSEL[i].maxSizeX(r, sizeX, sizeXBy, nuSEL[i].dragStartWidth);
			}
			
			for(var i = 0 ; i < nuSEL.length ; i++){
				nuSEL[i].setWidth(nuSEL[i].dragStartWidth + sizeXBy);
				nuSEL[i].setHeight(nuSEL[i].dragStartHeight + sizeYBy);
				if(arguments.length > 1){  //-- keystrokes
					nuSEL[i].dragStartWidth = nuSEL[i].getWidth() - (nuSEL[i].borderWidth * 2);
					nuSEL[i].dragStartHeight = nuSEL[i].getHeight() - (nuSEL[i].borderWidth * 2);
				}
			}
			if(nuSEL.length == 1 && document.getElementById('HeightProperty')){
				document.getElementById('HeightProperty').value  = nuSEL[0].getHeight() - (nuSEL[0].borderWidth * 2);
				document.getElementById('WidthProperty').value   = nuSEL[0].getWidth() - (nuSEL[0].borderWidth * 2);
			}
		}else{                                                             //-- move selected objects

			for(var i = 0 ; i < nuSEL.length ; i++){

				if(SectionID != '' && SectionID != nuSEL[i].sectionID){multipleSections = true;}
				SectionID             = nuSEL[i].sectionID;

				if(arguments.length > 1){  //-- keystrokes
					moveY                 = nuSEL[i].dragStartY + moveYBy;
					moveX                 = nuSEL[i].dragStartX + moveXBy;
				}else{
					moveY                 = nuSEL[i].dragStartY - lastO.offsetY + moveYBy;
					moveX                 = nuSEL[i].dragStartX - lastO.offsetX + moveXBy;
				}

				moveYBy               = nuSEL[i].maxMoveY(t, b, moveY, moveYBy);
				moveXBy               = nuSEL[i].maxMoveX(l, r, moveX, moveXBy);
			}

			for(var i = 0 ; i < nuSEL.length ; i++){

				if(nuSEL[i].id == nuSEL[i].id && arguments.length == 1){
					var offsetxBy     = lastO.offsetX;
					var offsetyBy     = lastO.offsetY;
				}else{
					var offsetxBy     = 0;
					var offsetyBy     = 0;
				}

				if(nuSEL[i].ControlType != 'PageBreak'){
					nuSEL[i].setLeft((nuSEL[i].dragStartX + moveXBy) - offsetxBy);
				}
				if(!multipleSections){  //-- if objects are all in 1 section
					nuSEL[i].setTop((nuSEL[i].dragStartY + moveYBy) - offsetyBy);
				}


			}
			if(nuSEL.length == 1 && document.getElementById('TopProperty')){
				var sec                                          = nuSEL[0].getSection();
				document.getElementById('TopProperty').value     = nuSEL[0].getTop() - sec.getTop();
				document.getElementById('LeftProperty').value    = nuSEL[0].getLeft();
			}
		}
	}
	
	
	
	function selectBoxedObjects(pID){
	
		var theHigh    = 0;
		var theLow     = 0;
		var oHigh      = 0;
		var oLow       = 0;
		var r          = document.getElementById(pID);
		var p          = r.parentNode;

		if(pID == 'nuLineDown'){                        //-- vertical box
			theLow     = parseInt(r.style.left);
			theHigh    = theLow + parseInt(r.style.width);
		}else{                                          //-- horizontal box
			theLow     = parseInt(r.style.top);
			theHigh    = theLow + parseInt(r.style.height);
		}
		p.removeChild(r);                               //-- remove drawn box

		for(var i = 0 ; i < nuOB.length ; i ++){
			if(pID == 'nuLineDown'){                        //-- vertical box
				oLow     = nuOB[i].getLeft();
				oHigh    = nuOB[i].getRight();
			}else{                                          //-- horizontal box
				oLow     = nuOB[i].getTop();
				oHigh    = nuOB[i].getBottom();
			}
			if(oLow >= theLow && oHigh <= theHigh){
				nuOB[i].selectMe();
			}
		}
		
	}

	function valueChanged(obj){	//-- Updates value entered into object div to the object properties dialog.

		if (objDialogOpen){
			var objectDialog = document.getElementById('ValueProperty');
			if (objectDialog != undefined) objectDialog.value = obj.value;
		}

	}
	
	function newObject() {

		var ID                                     = newObjectID();
		var l                                      = parseInt(document.getElementById('Detail').style.left) + 35;

		buildObject(ID,'0', '0', l,'90','20', 'white', '0px', 'black', 'black', 'Arial', '14px', 'normal', 'left', 'Field', '', 'false', '', '', 'solid', '0px', 'black');
		for(var i = 0 ; i < nuOB.length ; i ++){
			nuOB[i].unSelectMe();
		}
		var O                                      = getObject(ID);
		O.selectMe();

	}
	
	function buildObject(pID, pSection, pTop, pLeft, pWidth, pHeight, pBackgroundColor, pBorderWidth, pBorderColor, pForeColor, pFontName, pFontSize, pfontWeight, pTextAlign, pControlType, pGraph, pCanGrow, pFormat, pValue, pBorderStyle, pBorderWidtha, pBorderColora) {

		pValue                                     = pValue.replace(/&#39;/g,"'");
		var ID                                     = pID;
		var sectionDiv                             = document.getElementById(sectionNo[pSection]);
		var nuInput                                = document.createElement('input');
		
		
		if(pBorderStyle == ''){pBorderStyle        = 'solid';}
		if(pBorderColor == ''){pBorderColor        = 'black';}
		if(pBorderWidth == ''){pBorderWidth        = '0px';}

		nuInput.setAttribute('id', ID);

		if (nuInput.addEventListener){
			nuInput.addEventListener('blur',       function(){valueChanged(nuInput)}, false);
			nuInput.addEventListener('mousedown',  function(e){mouseDn(e,nuInput)}, false);
			nuInput.addEventListener('mouseup',    function(e){mouseUp(e,nuInput)}, false);
			nuInput.addEventListener('mouseover',  function(){mouseOverObject(nuInput)}, false);
			nuInput.addEventListener('dblclick',   function(){enterTextMode(nuInput)}, false);
		}else if (nuInput.attachEvent){
			nuInput.attachEvent('onblur',          function(){valueChanged(nuInput)}, false);
			nuInput.attachEvent('onmousedown',     function(){mouseDn(window.event,nuInput)}, false);
			nuInput.attachEvent('onmouseup',       function(){mouseUp(window.event,nuInput)}, false);
			nuInput.attachEvent('onmouseover',     function(){mouseOverObject(nuInput)}, false);
			nuInput.attachEvent('ondblclick',      function(){enterTextMode(nuInput)}, false);
		}
		
		nuInput.style.position                     = 'absolute';
		nuInput.style.top                          = (parseInt(sectionDiv.style.top) + parseInt(pTop)) + 'px';
		nuInput.style.left                         = (parseInt(pLeft) + 0) + 'px';
		nuInput.style.width                        = parseInt(pWidth)              + 'px';
		nuInput.style.height                       = parseInt(pHeight)             + 'px';
		nuInput.style.backgroundColor              = pBackgroundColor;
		nuInput.style.borderStyle                  = pBorderStyle;
		nuInput.style.borderWidth                  = parseInt(pBorderWidth) + 'px';
		nuInput.style.borderColor                  = pBorderColor;
		nuInput.style.color                        = pForeColor;
		nuInput.style.fontFamily                   = pFontName;
		if(pFontSize == 'NaNpx'){pFontSize         = '12px';}
		nuInput.style.fontSize                     = parseInt(pFontSize) + 'px';
		nuInput.style.fontWeight                   = pfontWeight;
		nuInput.style.textAlign                    = pTextAlign;
		nuInput.value                              = pValue;
		nuInput.className                          = 'nuObject';

		if(pControlType == 'PageBreak'){
			nuInput.style.width                = '40px';
			nuInput.style.height               = '0px';
			nuInput.style.left                 = adjustLeft() + 'px';
			nuInput.style.backgroundColor      = 'white';
			nuInput.style.borderStyle          = 'dotted';
			nuInput.style.borderColor          = 'black';
			nuInput.style.borderWidth          = '2px';
		}
		
		document.body.appendChild(nuInput);
		nuOB.push(new nuObject(pID, pControlType, pCanGrow, pFormat, pGraph, pBorderWidth, pBorderColor, pBorderStyle, pFontName, pfontWeight));
		nuOB[nuOB.length-1].sectionID          = sectionDiv.id;
		squeezeInObject(nuOB[nuOB.length-1]);

	}
	
	function squeezeInObject(pnuOB, pForceSectionID){  //-- makes section bigger to squeeze in an object
	
		var section                                   = pnuOB.getSection();
		if(arguments.length == 2){                     //-- use this section, not current section
			section                                   = getSection(pForceSectionID);
		}

		var OT                                        = pnuOB.getTop();
		var OH                                        = pnuOB.getHeight();
		var OB                                        = parseInt(pnuOB.borderWidth);
		var ST                                        = section.getTop();
		var SH                                        = section.getHeight();

		if(pnuOB.getBottom() > section.getBottom()){                        //-- if section needs to be bigger
			if(pnuOB.sectionID.indexOf('Foot') == -1){
				document.getElementById('secHeaderHeight').value =	adjustSections(pnuOB.sectionID, pnuOB.getBottom() - section.getBottom());
			}else{
				document.getElementById('secFooterHeight').value =	adjustSections(pnuOB.sectionID, pnuOB.getBottom() - section.getBottom());
			}
			showSectionProperties(pnuOB);
		}
	
	}
	
	function showSectionProperties(pnuOB){
		
		if(pnuOB.sectionID.length == 8){ //-- sortable section
			loadRadio(document.getElementById('radio'+pnuOB.sectionID.substr(7)));
		}
		if(pnuOB.sectionID == 'Detail'){ //-- detail section
			loadRadio(document.getElementById('detailSection'));
		}
		if(pnuOB.sectionID.substr(0,4) == 'Page'){ //-- page section
			loadRadio(document.getElementById('pageSection'));
		}
		if(pnuOB.sectionID.substr(0,6) == 'Report'){ //-- report section
			loadRadio(document.getElementById('reportSection'));
		}
	
	}

	function defaultZero(pValue){
		if(pValue == ''){
			return 0;
		}else{
			return parseInt(pValue);
		}
	}

	function refocusSection(pThis){	//-- Only allow sections to be added in the next available order position.

		var clickedNum = parseInt(pThis.id.substr(8));
		var startID = 'sortField';
		for (i = 0; i < 8; i++){

			var currentID = startID + i;
			if (document.getElementById(currentID).value == ''){
				var firstBlankRow = parseInt(i);
				if (clickedNum > firstBlankRow){	
					setTimeout(function(){document.getElementById(currentID).focus()},10);
					return;
				}
			}
		}
	}
	
	function rptPropChanged(prop){

		if(prop.id == 'rptWidthProperty'){
			if(isNaN(parseInt(prop.value))){
				alert('Invalid value');
				prop.value  = '900';
			}else{
				for(var i = 0; i < nuSE.length; i++){
						nuSE[i].setWidth(prop.value);
				}
			}
		}
		
		if(prop.id == 'rptHeightProperty'){
			if (isNaN(parseInt(prop.value))){
				alert('Invalid value');
				prop.value  = '1040';
			}
		}
		
	}
	
	function cloneObject(){
	
		var nuSEL                         = getSelectedObjects();
		nuHI                              = Array();
		for (i = 0; i < nuSEL.length; i++){	//-- Clone all selected objects
		
			var O                         = nuOB[nuSEL[i].index];           //-- object
			var I                         = O.inside.style;                //-- inside style
			var S                         = getSection(O.sectionID);       //-- section
			var ID                        = newObjectID();
			var dblBorder                 = O.borderWidth*2;
			buildObject(ID, S.reportIndex, O.getTop()- S.getTop(), O.getLeft(), O.getWidth()-dblBorder, O.getHeight()-dblBorder, I.backgroundColor, O.borderWidth, O.borderColor, I.color, I.fontFamily, I.fontSize, I.fontWeight, I.textAlign, O.ControlType, O.Graph, O.CanGrow, O.Format, O.inside.value, O.borderStyle, O.borderWidth, O.borderColor);
			var C                         = getObject(ID);   //-- newly created object

			C.setLeft(C.getLeft() + 10);
			C.setTop(O.getTop()  + 10);
			squeezeInObject(C);
			O.unSelectMe();
			C.selectMe();

		}
		
		if (objDialogOpen){
			openDialog();
		}
		
	}

	function bringToFront(pFront){
	
		var bArrays                           = Array();
		var spliceIDs                         = Array()
		for (var i = 0 ; i < nuOB.length ; i ++){	//-- Clone all selected objects
			if(nuOB[i].selected == pFront){	//-- do selected or unselected
			
				var O                         = nuOB[i];                       //-- object
				var I                         = O.inside.style;                 //-- inside style
				var S                         = getSection(O.sectionID);        //-- section
				var buildArray                = Array();
				buildArray.push(O.id);
				buildArray.push(S.reportIndex);
//				buildArray.push(O.getTop()-S.getTop());
//				buildArray.push(parseInt(I.top)-S.getTop());
				buildArray.push(parseInt(I.top) - parseInt(S.section.style.top));
				buildArray.push(I.left);
				buildArray.push(I.width);
				buildArray.push(I.height);
				buildArray.push(I.backgroundColor);
				buildArray.push(O.borderWidth);
				buildArray.push(I.borderColor);
				buildArray.push(I.color);
				buildArray.push(I.fontFamily);
				buildArray.push(I.fontSize);
				buildArray.push(I.fontWeight);
				buildArray.push(I.textAlign);
				buildArray.push(O.ControlType);
				buildArray.push(O.Graph);
				buildArray.push(O.CanGrow);
				buildArray.push(O.Format);
				buildArray.push(O.inside.value);
				buildArray.push(I.borderStyle);
				buildArray.push(O.borderWidth);
				buildArray.push(I.borderColor);
				bArrays.push(buildArray);
				O.inside.parentNode.removeChild(O.inside);
				spliceIDs.push(i);
			}
		}

		for (var i = spliceIDs.length - 1; i >= 0; i--){	//-- remove in reverse order
			nuOB.splice(spliceIDs[i],1);
		}
			

		for(var i = 0 ; i < bArrays.length ; i++){
		
			var bO                            = bArrays[i];
			buildObject(bO[0],bO[1],bO[2],bO[3],bO[4],bO[5],bO[6],bO[7],bO[8],bO[9],bO[10],bO[11],bO[12],bO[13],bO[14],bO[15],bO[16],bO[17],bO[18],bO[19],bO[20],bO[21]);

			if(pFront){
				var C                         = getObject(bO[0]);   //-- newly created object
				C.selectMe();
			}

		}
		adjustSections('Detail', 0);

		for(var I = 0 ; I < nuOB.length ; I++){ //-- reindex object array
			nuOB[I].index     = I;
		}

		if (objDialogOpen){
			openDialog();
		}
	}

	//-- This one deletes all selected objects with the delete shortcut key, 
	//-- or just one from the Object Properties Dialog	
	
	function deleteObjects(pJustOne){

		var nuSEL                     = Array();
		var mess                      = String();
		if(arguments.length == 1){   //-- delete just one object
			nuSEL.push(getObject(lastObjectClicked));
			mess                      = "Delete selected object?";
		}else{
			nuSEL                     = getSelectedObjects();
			mess                      = "Delete all selected objects?";
			if (nuSEL.length == 0 || doNotPasteObjects || selectMode == 'text'){
				return;
			}
		}

		if (confirm(mess)){
			for (var i = 0 ; i < nuSEL.length ; i++){
				var obj               = nuSEL[i];                        //-- delete object
				obj.inside.parentNode.removeChild(obj.inside);
				nuOB.splice(obj.index,1);
				for(var I = 0 ; I < nuOB.length ; I++){ //-- reindex object array
					nuOB[I].index     = I;
				}
			}
		}
	}


	function hideAllDialogs(){
	
		var b = document.body;
		for(i = 0; i < b.childNodes.length; i++){
			if(b.childNodes[i].className == 'nuDialog'){
					b.childNodes[i].style.visibility  = 'hidden';
			}
		}

	}

	function someObj() {  

		 this.someMethod = function() {  
			 alert('boo');  
		 }  
	 }  


	function testO(){
		o_obj = new someObj();  
		o_obj.someMethod(); //alerts "boo" 
	}

	function createTempInput()	//Offscreen text input field used to refocus to when required.
	{
		var tempElement = document.createElement("input");
		tempElement.setAttribute('type','text');
		tempElement.setAttribute('id','tempID');
		document.getElementById('tempDiv').appendChild(tempElement);		
	}
	
	function lineDown(e){
	
		if(!e){e=window.event;}
		var nuLine                    = document.createElement("div");
		nuLine.style.position         = 'absolute';
		nuLine.style.top              = '5px';
		nuLine.style.left             = e.clientX +'px';
		nuLine.style.width            = '0px';
		nuLine.style.height           = '900px';
		nuLine.style.borderStyle      = 'dotted';
		nuLine.style.borderWidth      = '1px';
		nuLine.style.borderColor      = 'black';
		
		nuLine.setAttribute('id', 'nuLineDown');
		document.body.appendChild(nuLine);
		theID                         = 'nuLineDown';
		theClass                      = 'Top_Margin';
		offsetX                       = e.clientX;
		
	}
	

	function lineAcross(e){
	
		if(!e){e=window.event;}
		var nuLine                    = document.createElement("div");
		nuLine.style.position         = 'absolute';
		nuLine.style.top              = e.clientY +'px';
		nuLine.style.left             = '5px';
		nuLine.style.width            = '1500px';
		nuLine.style.height           = '0px';
		nuLine.style.borderStyle      = 'dotted';
		nuLine.style.borderWidth      = '1px';
		nuLine.style.borderColor      = 'black';
		
		nuLine.setAttribute('id', 'nuLineAcross');
		document.body.appendChild(nuLine);
		theID                         = 'nuLineAcross';
		theClass                      = 'Left_Margin';
		offsetY                       = e.clientY;
		
	}
	
function sortDialog(){

	objDialogOpen                                 = false;
	var b = document.body;
	for(i = 0; i < b.childNodes.length; i++){                   //-- hide all dialog boxes
		if(b.childNodes[i].className == 'nuDialog'){
			b.childNodes[i].style.visibility      = 'hidden';
		}
	}
	document.getElementById("Sort_dialog").style.top        = '50px';
	document.getElementById("Sort_dialog").style.visibility = "visible"  		//-- open one dialog box
} 

function alignDialog(){

	objDialogOpen                                 = false;
	var b = document.body;
	for(i = 0; i < b.childNodes.length; i++){                   //-- hide all dialog boxes
		if(b.childNodes[i].className == 'nuDialog'){
			b.childNodes[i].style.visibility      = 'hidden';
		}
	}
	document.getElementById("Align_dialog").style.top        = '100px';
	document.getElementById("Align_dialog").style.visibility = "visible";
}

function reportDialog(pID){

	objDialogOpen = false;
	hideAllDialogs();
	document.getElementById(pID).style.top = '150px';
	document.getElementById(pID).style.visibility = "visible";  		//-- open one dialog box
}




function groupValues(){

	var S                      = getSelectedObjects();
	var A                      = Array();

	if(S.length == 0){return A;}  //-- nothing selected
	
	A['controltype']           = S[0].ControlType;
	A['thevalue']              = S[0].inside.value;
	A['cangrow']               = S[0].CanGrow;
	A['format']                = S[0].Format;
	A['graph']                 = S[0].Graph;
	A['color']                 = S[0].inside.style.color;
	A['fontsize']              = S[0].inside.style.fontSize;
	A['fontWeight']            = S[0].fontWeight;
	A['fontFamily']            = S[0].fontFamily;
	A['bgcolor']               = S[0].inside.style.backgroundColor;
	A['textalign']             = S[0].inside.style.textAlign;
	A['top']                   = S[0].getFromSectionTop() + S[0].borderWidth;
	A['left']                  = S[0].getLeft();
	A['width']                 = S[0].getWidth() - (S[0].borderWidth * 2);
	A['height']                = S[0].getHeight() - (S[0].borderWidth * 2);
	A['borderwidth']           = S[0].borderWidth;
	A['bordercolor']           = S[0].inside.style.borderColor;
	A['borderstyle']           = S[0].inside.style.borderStyle;
	
	for(var i = 0 ; i < S.length ; i ++){
		if(A['controltype']   != S[i].ControlType){                            A['controltype'] = '';}
		if(A['thevalue']      != S[i].inside.value){                           A['thevalue'] = '';}
		if(A['cangrow']       != S[i].CanGrow){                                A['cangrow'] = '';}
		if(A['format']        != S[i].Format){                                 A['format'] = '';}
		if(A['graph']         != S[i].Graph){                                  A['graph'] = '';}
		if(A['color']         != S[i].inside.style.color){                     A['color'] = '';}
		if(A['fontsize']      != S[i].inside.style.fontSize){                  A['fontsize'] = '';}
		if(A['fontWeight']    != S[i].fontWeight){                             A['fontWeight'] = '';}
		if(A['fontFamily']    != S[i].fontFamily){                             A['fontFamily'] = '';}
		if(A['bgcolor']       != S[i].inside.style.backgroundColor){           A['bgcolor'] = '';}
		if(A['textalign']     != S[i].inside.style.textAlign){                 A['textalign'] = '';}
		if(A['top']           != S[i].getFromSectionTop() + S[i].borderWidth){ A['top'] = '';}
		if(A['left']          != S[i].getLeft()){                              A['left'] = '';}
		if(A['width']         != S[i].getWidth()  - (S[i].borderWidth * 2)){   A['width'] = '';}
		if(A['height']        != S[i].getHeight() - (S[i].borderWidth * 2)){   A['height'] = '';}
		if(A['borderwidth']   != S[i].borderWidth){                            A['borderwidth'] = '';}
		if(A['bordercolor']   != S[i].inside.style.borderColor){               A['bordercolor'] = '';}
		if(A['borderstyle']   != S[i].inside.style.borderStyle){               A['borderstyle'] = '';}
	}
	
	return A;

}


function openDialog(){

	objDialogOpen                                         = true;
	var b                                                 = document.body;

	for(i = 0; i < b.childNodes.length; i++){                   //-- hide all dialog boxes
		if(b.childNodes[i].className == 'nuDialog'){
			b.childNodes[i].style.visibility              = 'hidden';
		}
	}
	
	var currDialog     = document.getElementById("OP_dialog");				//-- remove all old dialog box fields, before new ones are created
	for (i = currDialog.childNodes.length - 1; i > 0; i--){
		if (currDialog.childNodes[i].className == 'objDialog'){
			currDialog.removeChild(currDialog.childNodes[i]);
		}
	}
	
	var nuSEL                                                 = getSelectedObjects();

	if (nuSEL.length  == 0){
		document.getElementById("OP_dialog").style.visibility = "visible"  		//-- open one dialog box
		return;
	}

	var GV                                                    = groupValues();
	var T                                                     = Array();
	var CT                                                    = '';
	
	for(var i = 0 ; i < nuSEL.length ; i ++){
		CT                                                    = CT + nuSEL[i].ControlType.substr(0,1);
	}

	if(nuSEL.length == 1){createPropertyDiv('controltype','Control Type','select','ControlTypeProperty',theControl, GV['controltype']);}
	if(CT.indexOf('P') == -1){
		createPropertyDiv('left','Left','input','LeftProperty','text', GV['left']);
		createPropertyDiv('top','Top','input','TopProperty','text', GV['top']);
		createPropertyDiv('height','Height','input','HeightProperty','text', GV['height']);
		createPropertyDiv('width','Width','input','WidthProperty','text', GV['width']);
		if(CT.indexOf('G') == -1){createPropertyDiv('bgcolor','Background Color','input','BackgroundColorProperty','text', GV['bgcolor']);}
		createPropertyDiv('bordercolor','Border Color','input','BorderColorProperty','text', GV['bordercolor']);
		createPropertyDiv('borderwidth','Border Width','input','BorderWidthProperty','text', GV['borderwidth']);
		if(CT == 'F'){createPropertyDiv('cangrow','Can Grow','select','CanGrowProperty',canGrow, GV['cangrow']);}
		if(CT =='F'){createPropertyDiv('thevalue','Field','input','ValueProperty','text', GV['thevalue']);}
		if(CT.indexOf('G') == -1){createPropertyDiv('color','Font Color','input','ColorProperty','text', GV['color']);}
		if(CT.indexOf('G') == -1){createPropertyDiv('fontFamily','Font Family','select','FontFamilyProperty',theFamily, GV['fontFamily']);}
		if(CT.indexOf('G') == -1){createPropertyDiv('fontsize','Font Size','input','FontSizeProperty','text', GV['fontsize']);}
		if(CT.indexOf('G') == -1){createPropertyDiv('fontWeight','Font Weight','select','fontWeightProperty',theWeight, GV['fontWeight']);}
		if(CT == 'F'){createPropertyDiv('format','Format','select','FormatProperty',theFormat, GV['format']);}
		if(CT == 'G'){createPropertyDiv('graph','Graph','select','GraphProperty',theGraph, GV['graph']);}
		if(CT =='L'){createPropertyDiv('thevalue','Label','input','ValueProperty','text', GV['thevalue']);}
		if(CT =='G'){createPropertyDiv('thevalue','Parameters','input','ValueProperty','text', GV['thevalue']);}
		if(CT.indexOf('G') == -1){createPropertyDiv('textalign','Text Align','select','TextAlignProperty',theAlign, GV['textalign']);}

	}
	document.getElementById("OP_dialog").style.visibility = "visible"  		//-- open one dialog box	
}



function createPropertyDiv(divID, theInnerHTML, inputType, inputID, inputTypeTwo, theValue){
	
	var ct                 = document.createElement("div"); //-- create div to hold object property 'row'
	ct.setAttribute('id',divID);
	ct.className           = 'objDialog';
	var ct2                = document.createElement("div");
	ct2.className          = "innerDiv";
	ct2.innerHTML          = theInnerHTML;
	document.getElementById("OP_dialog").appendChild(ct);
	document.getElementById(divID).appendChild(ct2);
	//and the input element for the property
	var cts                = document.createElement(inputType);
	cts.setAttribute('id',inputID);
	cts.className          = 'objDialog';

	if (inputType == "input"){
		cts.setAttribute('type',inputTypeTwo);
		cts.value          = theValue;
	}
	if (cts.addEventListener){
		cts.addEventListener('change', function(){propertyModified(inputID)}, false);
	}else if (cts.attachEvent){
		cts.attachEvent('onchange', function(){propertyModified(inputID)}, false);
	}
	document.getElementById(divID).appendChild(cts);
	if (inputType == "select"){
		dropdownOptions(inputID,inputTypeTwo,theValue);	//inputTypeTwo is the array of select options if we're making a select element.
	}
}


function dropdownOptions(inputID,selectArray,whichSelected){	//-- Takes as parameter the array which contains values used to populate select dropdowns.

	for (i=0; i < selectArray.length; i++){

		var theValue      = selectArray[i];
		var theValue2     = selectArray[i];
		if(inputID        == 'FormatProperty'){  //-- get Format id from textFormatsArray()
			var theValue  = i;
		}
		var theSelect     = document.getElementById(inputID);
		try{		//firefox
			var newOpt    = new Option(theValue2,theValue);
			theSelect.add(newOpt,null);
			if (theValue  == whichSelected) newOpt.selected = true;
		}
		catch (e){	//ie
			var newOpt    = new Option(theValue2,theValue);
			theSelect.add(newOpt,0);
			if (theValue  == whichSelected) newOpt.selected = true;
		}
	}
}



function propertyModified(inputID){		//-- Updates the report objects according to changes made in the properties dialog.

	var objectProperty            = document.getElementById(inputID).id;
	var theValue                  = document.getElementById(inputID).value;
	
	if (objDialogOpen){		//-- Object(s) property change

		var nuSEL                 = getSelectedObjects();

		if(objectProperty == 'TopProperty' || objectProperty == 'LeftProperty' || objectProperty == 'WidthProperty' || objectProperty == 'HeightProperty'){
			addUndoHistory();  //-- size or position changes can be undone
		}

		
		for (i = 0; i < nuSEL.length; i++){

			var selectedO         = nuSEL[i];
			var inside            = selectedO.inside;
			

			if (objDialogOpen && inside == undefined){
				return;
			}

			if(objectProperty == 'ControlTypeProperty' && nuSEL.length == 1){  //-- only make a change if one object is selected

				selectedO.ControlType                 = theValue;
				inside.style.borderStyle              = 'solid';
				if(theValue=='Graph'){
					inside.style.backgroundColor      = 'black';
					inside.style.color                = 'white';
				}
				if(theValue=='PageBreak'){
					selectedO.setHeight(0);
					selectedO.setLeft(adjustLeft());
					selectedO.setWidth(40);
					inside.style.backgroundColor      = 'white';
					inside.style.borderStyle          = 'dotted';
					inside.style.borderColor          = 'black';
					selectedO.setBorderWidth(2);
				}
				if(theValue=='Label' || theValue=='Field'){
					inside.style.backgroundColor      = 'white';
					inside.style.color                = 'black';
				}
			}
			if(objectProperty == 'fontWeightProperty'){
				inside.style.fontWeight               = theValue;
				selectedO.fontWeight                  = theValue;
			}
			if(objectProperty == 'FontFamilyProperty'){
				inside.style.fontFamily               = theValue;
				selectedO.fontFamily                  = theValue;
			}
			if(objectProperty == 'TextAlignProperty'){
				inside.style.textAlign                = theValue;
				inside.value                          = inside.value;
			}
			if(objectProperty == 'CanGrowProperty'){
				selectedO.CanGrow                     = theValue;
			}
			if(objectProperty == 'FormatProperty'){	
				selectedO.Format                      = theValue;
			}
			if(objectProperty == 'GraphProperty'){
				selectedO.Graph                       = theValue;
			}
			if(objectProperty == 'ValueProperty'){
				inside.value                          = theValue;
			}
			if(objectProperty == 'TopProperty'){
				var theS                              = selectedO.getSection();
				selectedO.sectionID                   = theS.id;
				selectedO.setTop(Number(theValue) + theS.getTop());    //-- move top
				squeezeInObject(selectedO, theS.id);                   //-- enlarge section if needed
				selectedO.setTop(Number(theValue) + theS.getTop());    //-- put object back into section
			}
			if(objectProperty == 'LeftProperty'){
				selectedO.setLeft(theValue);
			}
			if(objectProperty == 'WidthProperty'){
				document.getElementById(inputID).value = selectedO.setWidth(theValue);
			}
			if(objectProperty == 'HeightProperty'){
				selectedO.setHeight(theValue);
				squeezeInObject(selectedO);                          //-- resize section if need be
			}
			if(objectProperty == 'BorderWidthProperty'){
				selectedO.setBorderWidth(theValue);
				var theS                              = selectedO.getSection();
				selectedO.sectionID                   = theS.id;
				squeezeInObject(selectedO, theS.id);                   //-- enlarge section if needed
			}
			if(objectProperty == 'BorderColorProperty'){
				selectedO.setBorderColor(theValue);
			}
			if(objectProperty == 'BorderStyleProperty'){
				selectedO.setBorderStyle(theValue);
			}
			if(objectProperty == 'ColorProperty'){
				inside.style.color                    = theValue;
			}
			if(objectProperty == 'FontSizeProperty'){
				inside.style.fontSize                 = parseInt(theValue) +'px';
			}
			if(objectProperty == 'BackgroundColorProperty'){
				inside.style.backgroundColor          = theValue;
			}
			if(objectProperty == 'ControlTypeProperty' && nuSEL.length == 1){  //-- only make a change if one object is selected
				openDialog();
			}
		}
	}
}

function executeAlign(){

	var alignWay                         = document.getElementById('selectAlign').value;
	var nuSEL                            = getSelectedObjects();
	var pos                              = Number(0);
	addUndoHistory();
	
	if (alignWay=="Left"){

		pos = Number(99999);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.min(pos, nuSEL[i].getLeft());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setLeft(pos);
		}

	}

	
	if (alignWay=="Right"){

		pos                              = Number(0);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.max(pos, nuSEL[i].getRight());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setLeft(pos - nuSEL[i].getWidth());
		}

	}

	if (alignWay=="Top"){

		pos                              = Number(99999);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.min(pos, nuSEL[i].getTop());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setTop(pos);
		}

	}

	
	if (alignWay=="Bottom"){

		pos                              = Number(0);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.max(pos, nuSEL[i].getBottom());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setTop(pos - nuSEL[i].getHeight());
		}

	}


	if (alignWay=="Narrow"){

		pos                              = Number(99999);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.min(pos, nuSEL[i].getWidth());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setWidth(pos);
		}

	}

	
	if (alignWay=="Wide"){

		pos                              = Number(0);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.max(pos, nuSEL[i].getWidth());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setWidth(pos);
		}

	}


	if (alignWay=="Short"){

		pos                              = Number(99999);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.min(pos, nuSEL[i].getHeight());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setHeight(pos);
		}

	}

	
	if (alignWay=="Tall"){

		pos                              = Number(0);
		for (i = 0 ; i < nuSEL.length ; i ++){
			pos                          = Math.max(pos, nuSEL[i].getHeight());
		}
		for (i = 0 ; i < nuSEL.length ; i ++){
			nuSEL[i].setHeight(pos);
			squeezeInObject(nuSEL[i]);                          //-- resize section if need be
		}

	}

	if (alignWay=="Hspace"){

		nuSEL.sort(sortFromLeft);
		var totalWidth                 = 0;
		
		for (i = 1 ; i < nuSEL.length -1 ; i ++){  //-- from the second to the second last
			totalWidth                 = totalWidth + nuSEL[i].getWidth();
		}
		var distanceBetweenEnds = nuSEL[nuSEL.length-1].getLeft() - nuSEL[0].getRight();
		var gap                        = (distanceBetweenEnds - totalWidth) / (nuSEL.length - 1);

		pos                            = nuSEL[0].getRight() + gap;
		for (i = 1 ; i < nuSEL.length -1 ; i ++){  //-- from the second to the second last
			nuSEL[i].setLeft(pos);
			pos                        = pos + gap + nuSEL[i].getWidth()
		}

	}

	if (alignWay=="Vspace"){

		nuSEL.sort(sortFromTop);
		var totalHeight               = 0;
		
		for (i = 1 ; i < nuSEL.length -1 ; i ++){                  //-- from the second to the second last
			if(nuSEL[0].sectionID != nuSEL[i].sectionID){return;}  //-- selection spans more than one section
			totalHeight               = totalHeight + nuSEL[i].getHeight();
		}
		var distanceBetweenEnds = nuSEL[nuSEL.length-1].getTop() - nuSEL[0].getBottom();
		var gap                       = (distanceBetweenEnds - totalHeight) / (nuSEL.length - 1);
		var sec                       = getSection(nuSEL[0].sectionID);

		pos                           = nuSEL[0].getBottom() + gap;
		for (i = 1 ; i < nuSEL.length -1 ; i ++){                  //-- from the second to the second last
			nuSEL[i].setTop(pos);
			pos                       = pos + gap + nuSEL[i].getHeight()
		}

	}
	if (alignWay=="Front"){
		bringToFront(true);
	}

	if (alignWay=="Back"){
		bringToFront(false);
	}

}

function sortFromLeft(pFirst, pLast){

	if(pFirst.getLeft() > pLast.getLeft()){return  1;}
	if(pFirst.getLeft() < pLast.getLeft()){return -1;}
	return 0;

}


function sortFromTop(pFirst, pLast){

	if(pFirst.getTop() > pLast.getTop()){return  1;}
	if(pFirst.getTop() < pLast.getTop()){return -1;}
	return 0;

}



function loadRadio(pThis){

	for(var i = 0 ; i < radioButtons.length ; i++){
		document.getElementById(radioButtons[i]).checked = false;
	}
//nuDbug(123);
	pThis.checked = true;
	
	if(pThis.id == 'reportSection'){
		loadSectionProperties(1, 'Report');
		return;
	}
	if(pThis.id == 'pageSection'){
		loadSectionProperties(3, 'Page');
		return;
	}
	if(pThis.id == 'detailSection'){
		loadSectionProperties(0, 'Detail');
		return;
	}
	
	var s = pThis.id.substr(5);
	var headerIndex  = (Number(s) * 2) + 5;
	loadSectionProperties(headerIndex, document.getElementById('sortField' + s).value);

}


function loadSectionProperties(pHeaderNo, pSectionName){  //-- Load Section Properties into Sort Dialog

	var Header                                             = getSection(pHeaderNo);
	var Footer                                             = getSection(pHeaderNo+1);
	document.getElementById('secHeaderHeight').value       = '';
	document.getElementById('secHeaderColor').value        = '';
	document.getElementById('secHeaderName').value         = '';
	document.getElementById('secFooterName').value         = '';
	document.getElementById('secFooterHeight').value       = '';
	document.getElementById('secFooterColor').value        = '';

	if(pSectionName == ''){return;}                                          //-- no sort level

	document.getElementById('secHeaderHeight').value       = Header.getHeight();
	document.getElementById('secHeaderColor').value        = Header.getColor();
	document.getElementById('secHeaderName').value         = pSectionName + '_Header';

	if(pHeaderNo == 0){                                                      //-- Detail Section Has No Footer
		document.getElementById('secHeaderName').value     = pSectionName;   //-- Remove the word '_Header'
	}else{
		document.getElementById('secFooterHeight').value   = Footer.getHeight();
		document.getElementById('secFooterColor').value    = Footer.getColor();
		document.getElementById('secFooterName').value     = pSectionName + '_Footer';
	}

}


function applySortToSection(pID, pName, pThis){

	var HandF                              = getSectionsFromSortIndex(pID);
	var Header                             = HandF[0];
	var Footer                             = HandF[1];

	Header.renameSection(pName + '_Header');
	Footer.renameSection(pName + '_Footer');
	Header.sortField = pName + '_Header';
	Footer.sortField = pName + '_Footer';

	if(arguments.length == 3){
		document.getElementById('secHeaderName').value = pName + '_Header';
		document.getElementById('secFooterName').value = pName + '_Footer';
		leaveSortField(pThis, pID);
	}
}
	

	
function updateSectionProperties(){

	var Header                           = Object();
	var Footer                           = Object();
	var somethingChecked                 = false;
	var radioIndex                       = 0;
	var theHTML                          = '';
	
	for(var i = 0 ; i < radioButtons.length ; i++){
		if(document.getElementById(radioButtons[i]).checked){

			somethingChecked             = true;
			if(i == 10){                          //--detail section
				Header                   = getSection('Detail')
			}
			if(i == 0){                           //--report section
				Header                   = getSection('Report_Header')
				Footer                   = getSection('Report_Footer')
			}
			if(i == 1){                           //--page section
				Header                   = getSection('Page_Header')
				Footer                   = getSection('Page_Footer')
			}
			if(i > 1 && i < 8){                  //--a sortable section
				radioIndex               = i - 2;
				Header                   = getSection('Header_' + radioIndex)
				Footer                   = getSection('Footer_' + radioIndex)
				theHTML                  = document.getElementById('sortField' + radioIndex).value;
				Header.renameSection(theHTML + '_Header');
				Footer.renameSection(theHTML + '_Footer');
			}
		}
	}
	
	if(somethingChecked){  //-- a radio button was checked

		var HH                           = parseInt(document.getElementById('secHeaderHeight').value);
		var FH                           = parseInt(document.getElementById('secFooterHeight').value);

		if(isNaN(HH)){HH = 0;}
		if(isNaN(FH)){FH = 0;}

		var HC                           = document.getElementById('secHeaderColor').value;
		var FC                           = document.getElementById('secFooterColor').value;

		Header.setColor(document.getElementById('secHeaderColor').value);
		document.getElementById('secHeaderHeight').value     = adjustSections(Header.id, HH - Header.getHeight());

		if(Header.id != 'Detail'){           //-- not Detail section
			Footer.setColor(document.getElementById('secFooterColor').value);
			document.getElementById('secFooterHeight').value = adjustSections(Footer.id, FH - Footer.getHeight());
		}
	}
}


function selectSection(pSection){
	loadRadio(document.getElementById(pSection));
}


function enterSortField(pIndex){


	sortWas              = document.getElementById('sortField'+pIndex).value;
	if(pIndex > 0){
		var previousSort = document.getElementById('sortField'+(pIndex-1));
		if(previousSort.value == ''){
			previousSort.focus();
			return;
		}
	}
	loadRadio(document.getElementById('radio'+pIndex));

}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}


function leaveSortField(pThis, pIndex){

	if(trim(pThis.value) != ''){return;}                                                     //-- has a sort value
	if(pIndex == 7){return;}                                                           //-- its the last sort
	if(document.getElementById('sortField'+(Number(pIndex)+1)).value == ''){return;}   //-- the next sort is blank

	if(trim(document.getElementById('sortField' + pIndex).value) == ''){
		alert('Cannot be blank');
		pThis.value = sortWas;
	}

}

function stopObjectEdit(){
	var nuSEL  = getSelectedObjects();
	if(nuSEL.length == 1){
		nuSEL[0].selectMe();
	}

}

</script>

<?php

jsinclude('common.js');

?>

<body style='margin:30px;' onload='buildReport();' onmousemove='mouseMove(event)'  onmouseup='mouseUp(event,this)' onkeydown='keyDown(event)' onkeyup='keyUp(event)'>

<div id='Top_Margin'  class='Top_Margin' style='top:0px;left:20px;width:1200px;height:20px' onmousedown='lineDown(event)'></div>
<div id='Left_Margin' class='Left_Margin' style='top:0px;left:0px;width:20px;height:800px'   onmousedown='lineAcross(event)'></div>


<!-- Toolbar -->
<div style='left:22px; cursor:pointer;' class='nuToolbar' onclick='newObject()'  id='new_object'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>New Object</div>
<div style='left:122px; cursor:pointer;' class='nuToolbar' onclick='cloneObject()'  id='clone_object'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Clone Object</div>
<div style='left:222px; cursor:pointer;' class='nuToolbar' onclick='alignDialog()'  id='pick_objects'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Adjust Objects</div>
<div style='left:322px; cursor:pointer;' class='nuToolbar' onclick='sortDialog()'  id='sort_order'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Sections / Sort</div>
<div style='left:422px; cursor:pointer;' class='nuToolbar' onclick='openDialog()' id='object_properties'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Object Properties</div>
<div style='left:522px; cursor:pointer;' class='nuToolbar' onclick='reportDialog("Report_dialog")'  id='report_properties'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Report Properties</div>
<div style='left:622px; cursor:pointer;' class='nuToolbar' onclick='buildClass()'  id='copy_changes'  onmouseover='toolOver(this)' onmouseout='toolOut(this)' class='nuToolbar'>Copy Changes</div>

<!-- Sections -->
<div id='Report_Header' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("reportSection");}' ondblclick='sortDialog()' style='width:850px;left:22px;height:0px;top:50px'>Report_Header</div>
<div id='Page_Header'   class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("pageSection");}' ondblclick='sortDialog()' style='width:850px;left:22px;height:0px;top:102px'>Page_Header</div>
<div id='Header_0' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio0");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_1' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio1");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_2' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio2");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_3' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio3");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_4' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio4");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_5' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio5");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_6' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio6");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Header_7' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio7");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Detail'   class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("detailSection");}' ondblclick='sortDialog()' style='width:850px;left:22px;height:0px;top:154px'>Detail</div>
<div id='Footer_7' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio7");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_6' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio6");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_5' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio5");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_4' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio4");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_3' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio3");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_2' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio2");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_1' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio1");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Footer_0' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("radio0");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:154px'></div>
<div id='Page_Footer'   class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("pageSection");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:236px'>Page_Footer</div>
<div id='Report_Footer' class='nuSection' onclick='if(noSelections()){unselectObjects(this);selectSection("reportSection");}' ondblclick='sortDialog()'  style='width:850px;left:22px;height:0px;top:288px'>Report_Footer</div>


<!-- Object Dialog -->
<div
	id='OP_dialog' 
	class='nuDialog' 
	onmousedown='mouseDn(event,this)' 
	onmouseup='mouseUp(event,this)'
	style='height:520px;width:340px;top:150px;left:200px;'>
	<div id='OP_close' 
		style='cursor:pointer'
		class='nuClose' 
		onmousedown='closeDialog(this)' 
		onmouseover='mouseOver(this)' 
		onmouseout='mouseOut(this)'>
		X &nbsp
	</div>
	<div id='OP_title' 
		class='nuTitle' 
		style='width:340px' >
		&nbsp;&nbsp;Object Properties
	</div>
	<div id='OP_delete' style='position:absolute; left:233px; top:5px; width:120px; font-size:12px; color:yellow; cursor:pointer;' onMouseOver='mouseOver(this)' onMouseOut='mouseOut(this)' onClick='deleteObjects(1)'>Delete This Object</div>
</div>

<!-- Align Dialog -->
<div
	id='Align_dialog'
	class='nuDialog'
	onmousedown='mouseDn(event,this)' 
	onmouseup='mouseUp(event,this)'
	style='height:340px;width:230px;top:100px;left:221px; color:white; font-size:18px;'>
	<div id='OP_close' 
		style='cursor:pointer;'
		class='nuClose' 
		onmousedown='closeDialog(this)' 
		onmouseover='mouseOver(this)' 
		onmouseout='mouseOut(this)'>
		X &nbsp
	</div>
	<div id='OP_title' 
		class='nuTitle' 
		style='width:340px;' >
		&nbsp;&nbsp;Adjust Selected Objects
	</div>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select multiple="multiple" id='selectAlign' style='position:relative; font-size:14px; top:30px; height:260px; left:3px; padding:2px; width:160px;'>
    <optgroup label="--Adjust To-----------------">
	<option value='Left'>Left</option>
	<option value='Right'>Right</option>
	<option value='Top'>Top</option>
	<option value='Bottom'>Bottom</option>
	<option value='Narrow'>Narrowest</option>
	<option value='Wide'>Widest</option>
	<option value='Short'>Shortest</option>
	<option value='Tall'>Tallest</option>
    </optgroup>
    <optgroup label="--Evenly Space--------------">
	<option value='Hspace'>Horizontal Spacing</option>
	<option value='Vspace'>Vertical Spacing</option>
    </optgroup>
    <optgroup label="--Order---------------------">
	<option value='Front'>Bring To Front</option>
	<option value='Back'>Send To Back</option>
    </optgroup>
	</select>
	<br/>
	<br/>
	<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input style='position:relative; width:160px;' onClick='executeAlign();' type='button' value='Apply'>
</div>

<!-- Report Dialog -->
<div
	id='Report_dialog'
	class='nuDialog'
	onmousedown='mouseDn(event,this)'
	onmouseup='mouseUp()'
	style='height:150px;width:250px;top:50px;left:800px;'>
	<div id='OP_close' 
		style='cursor:pointer'
		class='nuClose' 
		onmousedown='closeDialog(this)' 
		onmouseover='mouseOver(this)' 
		onmouseout='mouseOut(this)'>
		X &nbsp;
	</div>
	<div id='OP_title' 
		class='nuTitle' 
		style='width:250px'>
		&nbsp;&nbsp;Report Properties
	</div>
	<div id="rptwidth" style='width:190px;position:absolute; top:30px; left:10px; color:white;'>
	Width<input onfocus='this.select()'  id='rptWidthProperty'          style='width:50px; position:absolute; left:100px' onChange='rptPropChanged(this)'>
	</div>
	<div id="rptheight" style='width:190px;position:absolute; top:60px; left:10px; color:white;'>
	Height<input onfocus='this.select()'  id='rptHeightProperty'        style='width:50px; position:absolute; left:100px' onChange='rptPropChanged(this)'>
	</div>
	<div id="rptpapertype" style='width:190px;position:absolute; top:90px; left:10px; color:white;'>
	Paper Type<input onfocus='this.select()'  id='rptPaperTypeInput'    style='width:100px;position:absolute; left:100px' onChange='rptPropChanged(this)'>
	</div>
	<div id="rptorientation" style='width:190px;position:absolute; top:120px; left:10px; color:white;'>
	Orientation<input onfocus='this.select()'  id='rptOrientationInput' style='width:50px; position:absolute; left:100px;' onChange='rptPropChanged(this)'>
	</div>
</div>

<!-- Section Dialog -->
<div 
	id='SP_dialog' 
	class='nuDialog' 
	onmousedown='mouseDn(event,this)' 
	onmouseup='mouseUp()'
	style='height:202px;width:340px;top:50px;left:900px;'>
	<div id='SP_close' 
		style='cursor:pointer'
		class='nuClose' 
		onmousedown='closeDialog(this)' 
		onmouseover='mouseOver(this)' 
		onmouseout='mouseOut(this)'>
		X &nbsp
	</div>
	<div id='SP_title' 
		class='nuTitle' 
		style='width:800px' >
		&nbsp;&nbsp;Section Properties
	</div>

</div>


<!-- Sort Order Dialog -->
<div
	id='Sort_dialog'
	class='nuDialog'
	onmousedown='mouseDn(event,this)'
	onmouseup='mouseUp()'
	style='height:520px;width:400px;top:50px;left:600px;'>
	<div id='OP_close' 
		style='cursor:pointer'
		class='nuClose' 
		onmousedown='closeDialog(this)' 
		onmouseover='mouseOver(this)' 
		onmouseout='mouseOut(this)'>
		X &nbsp;
	</div>
	<div id='OP_title' 
		class='nuTitle' 
		style='width:450px' >
		&nbsp;&nbsp;Sections / Sort Order
	</div>

	<!-- Report Radio -->
		<input id="reportSection" name="reportSection"  type="radio" onclick='loadRadio(this)' style='position:absolute;top:25px;left:40px'>
		<input onfocus='loadRadio(document.getElementById("reportSection"))' id='ReportRadio' value='Report' readonly='readonly'  style='position:absolute;top:25px;left:65px;background-color:lightgrey;width:150px'>
		
	<!-- Page Radio -->
		<input id="pageSection"  name="pageSection"  type="radio" onclick='loadRadio(this)' style='position:absolute;top:50px;left:40px'>
		<input onfocus='loadRadio(document.getElementById("pageSection"))' id='PageRadio' value='Page'  readonly='readonly' style='position:absolute;top:50px;left:65px;background-color:lightgrey;width:150px'>

<?php

	for ($i = 0 ; $i < 8 ; $i++){
		$top = ((3 + $i) * 25) . 'px';
		print "<!-- Section $i Radio -->\n\n";
		print "<input id='radio$i' name='radio$i' type='radio'  onclick='loadRadio(this)' value='' style='position:absolute;top:$top;left:40px'>\n";
		print "<input  onfocus='enterSortField($i);this.select()' id='sortField$i' onChange='applySortToSection(this.id.substr(9), this.value, this);' onMouseDown='refocusSection(this)' value=''  style='position:absolute;top:$top;left:65px;width:150px'>\n";
		print "<select onfocus='enterSortField($i);' id='sort0$i' class='objects'  style='position:absolute;top:$top;left:225px;width:100px'>\n";
		print "    <option selected value=''>Ascending</option>\n";
		print "	   <option          value='DESC'>Descending</option>\n";
		print "    </select>\n";

	}
?>
	
// onfocus='loadRadio(document.getElementById(\"radio$i\"))'	
	<!-- Detail Radio -->
	<input id="detailSection" name="detailSection" type="radio"  onclick='loadRadio(this)' style='position:absolute;top:275px;left:40px'>
	<input onfocus='loadRadio(document.getElementById("detailSection"))' id='DetailRadio' value='Detail' readonly='readonly' style='position:absolute;top:275px;left:65px;background-color:lightgrey;width:150px'>
	
	<!-- Hard coding the section dialog into sort dialog -->
	<div class="sortDialog" style="height: 10px; "></div>
	<div id="sectionnameone" class="sortDialog" style='top:320px;' ><div style='text-align:right;width:150px' class="innerDiv">Section Name</div><input onfocus='this.select()'  id="secHeaderName" class="sortDialog" readonly='readonly' type="text" style='left:160px'></div>
	<div id="heightone" class="sortDialog" style='top:321px;' ><div style='text-align:right;width:150px' class="innerDiv">Height</div><input onfocus='this.select()'  id="secHeaderHeight" onchange='updateSectionProperties()' class="sortDialog" type="text" style='left:160px'></div>
	<div id="bgcolorone" class="sortDialog" style='top:322px;'><div style='text-align:right;width:150px' class="innerDiv">Background Color</div><input onfocus='this.select()'  id="secHeaderColor" onchange='updateSectionProperties()' class="sortDialog" type="text" style='left:160px'></div>
	<div class="sortDialog" style="height: 10px; "></div>
	<div id="sectionnametwo" class="sortDialog" style='top:323px;' ><div style='text-align:right;width:150px' class="innerDiv">Section Name</div><input onfocus='this.select()'  id="secFooterName" class="sortDialog" readonly='readonly' type="text" style='left:160px'></div>
	<div id="heighttwo" class="sortDialog" style='top:324px;' ><div style='text-align:right;width:150px' class="innerDiv">Height</div><input onfocus='this.select()'  id="secFooterHeight" onchange='updateSectionProperties()' class="sortDialog" type="text" style='left:160px'></div>
	<div id="bgcolortwo" class="sortDialog" style='top:325px;'><div style='text-align:right;width:150px' class="innerDiv">Background Color</div><input onfocus='this.select()'  id="secFooterColor" onchange='updateSectionProperties()' class="sortDialog" type="text" style='left:160px'></div>
	
</div>

<div id="tempDiv" style="position:absolute; top:-400px"></div> 						<!--Used for refocusing text objects when required. -->

<div style='position:absolute;top:0px;left:0px:width:0px;height;visibility:hidden'> <!--Holds php class string when built. -->
   <textarea rows='28' cols='70' name='classcode' id='classcode'></textarea>
</div>
</body>


</html>

<?php

function losePX($pixels){

	return str_replace ( 'px' , '' , $pixels);
	
}


?>
