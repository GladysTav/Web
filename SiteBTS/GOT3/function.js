$(document).ready(function() {

            $('#btn-get-ville').click(function traitement() {
            $.ajax("https://api.got.show/api/cities/"+$('#_name').val(),
            {
            type: "GET",
            data: $('#_name').val(),
            success: function (resultat) {
                        
            $("#id").html(resultat.data["_id"]);
            $("#name").html(resultat.data["name"]);
            $("#type").html(resultat.data["type"]);
            $("#X").html(resultat.data["coordX"]);
            $("#Y").html(resultat.data["coordY"]);
            }
            });
            
            });




$('#btn-get-perso').click(function traitement() {
            $.ajax("https://api.got.show/api/characters/"+$('#_name').val(),
            {
            type: "GET",
            data: $('#_name').val(),
            success: function (resultat) {
                        
            $("#_id").html(resultat.data["_id"]);
            $("#male").html(resultat.data["male"]);
            $("#house").html(resultat.data["house"]);
            $("#slug").html(resultat.data["slug"]);
            $("#name").html(resultat.data["name"]);
            $("#__v").html(resultat.data["__v"]);
            $("#c").html(resultat.data["c"]);
            $("#pageRank").html(resultat.data["pageRank"]);
            $("#books").html(resultat.data["books"]);
            $("#updatedAt").html(resultat.data["updatedAt"]);
            $("#createdAt").html(resultat.data["createdAt"]);
            $("#titles").html(resultat.data["titles"]);

            }
            });
            
            });


                        

            $('#btn-get-villes').click(function () {
                        $('#table-villes').bootstrapTable({
                                    url: 'https://api.got.show/api/cities',
                                    columns: [{
                                                
                                                field: 'name',
                                                title: 'Item Name'
                                    }, {
                                                field: 'type',
                                                title: 'type'
                                    }]
                        });
            });
            
            $('#btn-get-maisons').click(function () {
                        $('#table-maisons').bootstrapTable({
                                    url: 'https://api.got.show/api/houses',
                                    columns: [{
                                                
                                                field: 'name',
                                                title: 'Nom'
                                    }]
                        });
            });
});