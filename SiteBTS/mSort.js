<script>
/**
 * @author Le_chomeur
 */
var mSort = (function(){
 
	var M$ = function(e){return document.getElementById(e);};
	var M$$ = function(e,p){return p.getElementsByTagName(e)};
	var Mh = function(t){
				var lstHead = M$$('td',M$$('thead',t)[0]);
				if(lstHead.length > 0)
					return lstHead;
				else return false;
			};
	var addEvent = function(func,onEvent,elementDom) { 
					    if (window.addEventListener) { 
							elementDom.addEventListener(onEvent, func, false); 
						} else if (document.addEventListener) { 
							elementDom.addEventListener(onEvent, func, false); 
						} else if (window.attachEvent) { 
							elementDom.attachEvent("on"+onEvent, func); 
					    } 
					};
	return {
		tabSort : null,
		Create : function(tab){
			this.tabSort = M$(tab);
			var lstH = Mh(M$(tab));
			//Si on trouve des entêtes de type TH , on va ajouter les évènements et propriétés d'odre de tri :
			// 0 : descendant
			// 1 : ascendant
			if(lstH){
				for(var i = 0 , imax = lstH.length ; i < imax; i++){
					//Affectation du click sur l'entête
					addEvent(function(indexCol){return function(){
						if(this.order == undefined || this.order == 1){
							this.order = 0;
						}
						else{
							this.order = 1;
						}
					var order = (this.order == 0 )? true:false;
					mSort.SortCol(indexCol , order);	
					}}(i)
					,"click",lstH[i]);
 
				}
			}
			else{
				alert("Le tableau ne comporte pas d'entête");
			}
		},
		SortCol : function(indexCol,asc){
			//Récupération de toutes les lignes du tableau
			var lstTr = M$$('tr',this.tabSort);
			var tabSortable = new Array();
			//Récupération de toute les lignes
			for(var i = 1 , imax = lstTr.length ; i < imax; i++){
				var lstTd = M$$('td',lstTr[i]);
				tabSortable.push(lstTd[indexCol]);
				//Clone des cellules
				tabSortable[i-1][0] = new Array();
				for(var x = 0 , xmax = lstTd.length ; x < xmax; x++){
					tabSortable[i-1][0].push(lstTd[x].cloneNode(true));
				}
			}
			//Tri du tableau
			tabSortable.sort((asc)? sortAsc:sortDsc);
			//Création des lignes dans le nouvel ordre
			var n = document.createElement('tbody');
			for(var i = 0 , imax = tabSortable.length ; i < imax; i++){
				var l = document.createElement('tr');
				for (var x = 0, xmax = tabSortable[i][0].length; x < xmax; x++) {
					l.appendChild(tabSortable[i][0][x]);					
				}
				n.appendChild(l);
			}
			var o = M$$('tbody',this.tabSort)[0];
			this.tabSort.replaceChild(n,o);
		},
	}
})(); 
 
function sortDsc(b,a)
{
	//On commence par mettre en minuscule pour tester
	a= a.innerHTML.toLowerCase(), b=b.innerHTML.toLowerCase();
	//test si c'est une date au format ( 01/01/2010 ) avec le séparateur /
	da = Date.parse(a);
  	db = Date.parse(b);
	if(!isNaN(da) &&!isNaN(db)) {
     a = da;
     b = db
	}
	//Vérification sur les nombre ( entier et a virgule
	na = parseFloat(a) , nb = parseFloat(b);
	//Si ce n'est pas un chiffre
	if(isNaN(na) && isNaN(nb)){
      	return a > b ? 1 : (a < b ?- 1 : 0);
	}
	return na-nb;
}
function sortAsc(a,b)
{
	//On commence par mettre en minuscule pour tester
	a= a.innerHTML.toLowerCase(), b=b.innerHTML.toLowerCase();
	//test si c'est une date au format ( 01/01/2010 ) avec le séparateur /
	da = Date.parse(a);
  	db = Date.parse(b);
	if(!isNaN(da) &&!isNaN(db)) {
     a = da;
     b = db
	}
	//Vérification sur les nombre ( entier et a virgule
	na = parseFloat(a) , nb = parseFloat(b);
	//Si ce n'est pas un chiffre
	if(isNaN(na) && isNaN(nb)){
      	return a > b ? 1 : (a < b ?- 1 : 0);
	}
	return na-nb;
}
</script>