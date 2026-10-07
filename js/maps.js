import * as maplibregl from '@coop-site-manager/maplibregl'

class DropdownControl {
  onAdd(map) {
    this._map = map;
    this._container = document.createElement("div");
    this._container.className = "maplibregl-ctrl maplibregl-ctrl-group";

    const details = document.createElement("details");
    details.className = "maplibregl-ctrl-dropdown";

    const summary = document.createElement("summary");
    summary.textContent = "Directions";
    details.appendChild(summary);

    const fragment = document.createDocumentFragment();
    const li = fragment
      .appendChild(document.createElement("ul"))
      .appendChild(document.createElement("li"));
    li.textContent = 'Hello, world';

    details.appendChild(fragment);

    this._container.appendChild(details);

    return this._container;
  }

  onRemove() {
    this._container.remove();
    this._map = undefined;
  }
}

let configData = {};
const jsonDataScriptTag = document.getElementById(
  'wp-script-module-data-@coop-site-manager/map'
);
if (jsonDataScriptTag?.textContent) {
  try {
    configData = JSON.parse(jsonDataScriptTag.textContent);
  } catch { }
}

console.log(configData);

const map = new maplibregl.Map({
  container: 'map', // container id
  style: 'https://tiles.openfreemap.org/styles/liberty',
  center: [-105, 53], // starting position [lng, lat]
  zoom: 2, // starting zoom
  // attributionControl: false
});

// map.addControl(new maplibregl.AttributionControl({compact: true}));
map.addControl(new DropdownControl(), 'top-left');
