/*
LICENCE
*******
CSF Custom Select Form jquery Plugin
@Author : Denis Pissoort
is shared under the terms of the licence Creative Commons Paternité-Partage des Conditions Initiales à l'Identique 2.0 Belgique.
For more information : Author's work Page : http://dipi-graphics.com/labs.html
                       Licence Page : http://creativecommons.org/licenses/by-sa/2.0/be/
*/
(function($) {
        // define Plugin
        $.fn.customSelectForm = function(params) {
                //attach Params
                params = $.extend( {prefix: 'csf', icon:false}, params);
                // init
			    this.each(function() {
				var identity = params.prefix+$(this).index(); // ID CONTAINER OF THE Custom Select Box 
				$(this).wrap('<div class="'+identity+'"></div>'); // Wrap <select></select> with the DIV CONTAINER
                var selectBox = $(this); //Shortcut orginal <select> 
				var OptionsArray = selectBox.find('option').get(); // Array of <options>
				var numOptions = OptionsArray.length; // number of options
				var prefix = params.prefix+'_'; // prefix to add to class
				var indexSelected = selectBox.find('option:selected').index(); // index of the selected element
               	var page = $('html').not($('div[class^="'+params.prefix+'"]'.children)); // select all Element that are not use this plugin. => AutoClose List
				
				//REPLACE selectBox with Ul Li simulation
				var divClass = prefix+'input';	//div simulate input Selected
				var ulClass = prefix+'ul'; // list
				var liClass = prefix+'li'; // list item
				var btnClass = prefix+'btn'; // btn to open/close list
				
				$('div.'+identity).append('<div class="'+divClass+'"><p><span class="itemSelected">'+$(OptionsArray[indexSelected]).text()+'</span><a href="#" class="'+btnClass+'"></a></p></div>');
				$('div.'+identity).append('<ul class="'+ulClass+'"></ul>');
				if(params.icon){
				for(i=0; i<numOptions; i++){
					$('div.'+identity+' ul.'+ulClass).append('<li class="'+liClass+'"><a href="#" title="'+$(OptionsArray[i]).text()+'"><img src="'+$(OptionsArray[i]).attr("title")+'" class="iconCSF" alt="'+$(OptionsArray[i]).text()+' icon"/>'+$(OptionsArray[i]).text()+'</a></li>');
					if(i==indexSelected){ $('div.'+identity+' ul.'+ulClass).find('li:eq('+indexSelected+')').addClass('selected') } //set correct style for item selected
				}
				}else{
				for(i=0; i<numOptions; i++){
					$('div.'+identity+' ul.'+ulClass).append('<li class="'+liClass+'"><a href="#" title="'+$(OptionsArray[i]).text()+'">'+$(OptionsArray[i]).text()+'</a></li>');
					if(i==indexSelected){ $('div.'+identity+' ul.'+ulClass).find('li:eq('+indexSelected+')').addClass('selected') } //set correct style for item selected
				}
				}

				//HIDE LIST and ORIGINAL SELECT
				$('div.'+identity+' ul.'+ulClass).hide();
				selectBox.hide();

				//Event HANDLER
				// --- OPEN CLOSE ITEMS LIST ---
				function openCloseList(){
					//hide otherSelectBox ( in case of another selectBox with same prefix are opened )
					var otherSelectBox = $('ul[class^="'+params.prefix+'"]').not($('div.'+identity+' ul.'+ulClass));
					otherSelectBox.prev().children('p').children('a.'+btnClass).removeClass('opened');
					otherSelectBox.hide();
					// open the selectBox
					$('div.'+identity+' ul.'+ulClass).slideToggle('fast', bindableList);
					//add or remove "opened" class for button
					$('div.'+identity+' a.'+btnClass).toggleClass('opened');
					// unbind the click event to prevent multi click animation
					$(this).unbind('click', openCloseList);
					return false;
				}
				// --- SELECT ITEM IN THE ITEMS LIST ---
				function selectListItem(){
					$('div.'+identity+' ul.'+ulClass).find('li:eq('+indexSelected+')').removeClass('selected');
					indexSelected = $('div.'+identity+' ul.'+ulClass).find('li').index($(this).parent());
					$('div.'+identity+' ul.'+ulClass).find('li:eq('+indexSelected+')').addClass('selected');
					var txtItem = $(this).text();
					$('div.'+identity+' div.'+divClass+' p span.itemSelected').text(txtItem);
					rebuildSelect();
					$('div.'+identity+' ul.'+ulClass).slideToggle('fast');
					$('div.'+identity+' a.'+btnClass).toggleClass('opened');
					return false;		
				}
				// --- PREVENT MULTI CLICK ANIMATION ---
				function bindableList(){
					$('div.'+identity+' div.'+divClass).bind('click', openCloseList);
				}
				// --- AUTO CLOSE ALL LIST IF OPENED ---
				function autoClose(){
					if($('div.'+identity+' ul.'+ulClass).css('display') !== "none"){
						$('div.'+identity+' ul.'+ulClass).hide();
						$('div.'+identity+' a.'+btnClass).toggleClass('opened');
					}
				}

				//Bind Events
				bindableList();
				$('div.'+identity+' ul.'+ulClass+' a').bind('click', selectListItem	);
				page.bind('click', autoClose);
				
				
				//Rebuild original Select
				function rebuildSelect(){
					selectBox.html('');
					for(i=0; i<numOptions; i++){
						if(i==indexSelected){selectBox.append('<option value="'+$(OptionsArray[i]).val()+'" selected="selected">'+$(OptionsArray[i]).text()+'</option>');}
						else{
							selectBox.append('<option value="'+$(OptionsArray[i]).val()+'">'+$(OptionsArray[i]).text()+'</option>');
						} 
					}
				}

});
      // Permettre le chaînage par jQuery
        return this;
        };
})(jQuery);