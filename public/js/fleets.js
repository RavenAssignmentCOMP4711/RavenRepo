$(document).ready(function(){
    //console.log('hello');
    $("option:first-child").attr({'disabled':''}); 
    $("#plane_list").on('change', function(e){
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value; 
        //console.log(valueSelected);
        getPlane(valueSelected);
    })
});

function getPlane(planeId) {
    var url = "/wacky/plane/" + planeId;
    $.getJSON(url, function(data){
        for (var key in data) {
            if (key == 'id')
                continue;
            $('#'+key).val(data[key]); 
        }
    });
}
