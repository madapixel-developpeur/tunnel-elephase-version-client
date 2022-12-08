
let map = null;
let marqueur = null;
let geoJSON = null;
let circles = [];


window.onload = () => {
    let mapDOM = document.getElementById("map");
    let parcel = {};
    parcel.lat = mapDOM.dataset.lat;
    parcel.lon = mapDOM.dataset.lon;
    parcel.radius = mapDOM.dataset.radius;
    console.log(parcel);

    let myPosition = [parcel.lat, parcel.lon];
    let zoom = 15;

    map = L.map('map');
    map.setView(myPosition, 1);

    L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Aromaforest &copy;',
        minZoom: 1,
        maxZoom: 17
    }).addTo(map);

    // On dessine le marqueur si l'utilisateur a déjà les coordonnés
    if(myPosition) {
        marqueur = L.marker(myPosition);
        marqueur.addTo(map);
        map.flyTo(myPosition, zoom);
        let circleLayer = new L.LayerGroup().addTo(map); 
        let circleOptions = {radius: parcel.radius, fill : true, fillColor : 'blue', color : "blue", opacity : 0.3, fillOpacity: 0.15};       
        map.on('zoomend', function () {
            addCircleToGroup(circleLayer, parcel.lat,parcel.lon, circleOptions);
        });
    }
    
    function addCircleToGroup(group, lat, lon, options){
        let tmp = {
          lat:lat,
          lon:lon,
          radius:options.radius
        };
        if(map == undefined || JSON.stringify(circles).includes(JSON.stringify(tmp))) return;
        L.circle([lat,lon], options).addTo(group);
        circles.push(tmp);
    }
    // Hide loader
    document.getElementById("loading_aroma").classList.add('hide');

}
// http://localhost:8000/map?lat=-18.4721&lon=48.467&radius=1000