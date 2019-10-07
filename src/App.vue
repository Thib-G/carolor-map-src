<template>
  <div id="app" class="container">
    <div class="row">
      <div class="col-12 col-lg-6 mt-3">
        <div class="sticky-top">
          <h2 class="partners text-center"><img src="@/assets/img/carolor-logo.svg" height="72">
          Partenaires</h2>
            <div class="text-center">
              <button type="button"
                      class="btn btn-primary"
                      @click="locate">
                <i class="fa fa-location-arrow" aria-hidden="true">
                </i>&nbsp;&nbsp;Localisez-moi&nbsp;!
              </button>
            </div>
            <div ref="map" class="map mt-2"></div>
            <div class="d-flex justify-content-between">
              <a href="https://carolor.org/" target="_blank">carolor.org</a>
              <a href="https://github.com/Thib-G/carolor-map-src" target="_blank">
                <i class="fa fa-github" aria-hidden="true"></i>
              </a>
            </div>
        </div>
      </div>
      <div class="col-12 col-lg-6 mt-3 mb-3">
        <div class="card border-dark">
          <div class="list-group list-group-flush">
            <a class="list-group-item list-group-item-action d-flex justify-content-between"
                v-for="(m, index) in markersSorted"
                :key="m.id"
                href @click.prevent="showModal(m)">
              <span>
                <small>{{ index + 1}}.</small>&nbsp;
                <a href @click.prevent="showModal(m)">{{ m.name }}</a>&nbsp;
                <span class="badge badge-pill badge-warning">
                  {{ (m.distance / 1000).toFixed(1) }} km</span>
              </span>
              <button type="button"
                      class="btn btn-primary btn-sm"
                      @click.prevent.stop="zoomTo(m)">
                <i class="fa fa-search"></i>&nbsp;&nbsp;Zoom
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mModal"
      class="modal fade"
      id="exampleModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">{{ mModal.name }}
              <small><small><br />{{ mModal.address }}</small></small></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div v-html="mModal.popup"></div>
          </div>
          <div class="modal-footer">
            <a class="btn btn-primary"
              role="button"
              :href="`https://www.google.com/maps/?daddr=${mModal.lat},${mModal.lng}`"
              target="_blank">
              <i class="fa fa-map-o" aria-hidden="true">
              </i>&nbsp;&nbsp;Itinéraire Google Maps</a>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue';
import L from 'leaflet';
import $ from 'jquery';
import 'bootstrap';
import { json as d3Json } from 'd3-fetch';
import { setTimeout } from 'timers';

import PopupContentComponent from '@/components/PopupContentComponent.vue';

export default {
  name: 'app',
  data() {
    return {
      name: 'Carol\'Or',
      map: null,
      charleroi: [50.411609, 4.444551],
      markers: [],
      lg: L.layerGroup(),
      locationGroup: L.layerGroup(),
      myLocation: [50.411609, 4.444551],
      mModal: null,
      popupComponent: null,
    };
  },
  mounted() {
    this.initMap();
    this.getMarkers().then(this.udpateMarkersOnMap);
    setTimeout(this.locate, 1000);
  },
  watch: {
    markers() {
      this.udpateMarkersOnMap();
    },
  },
  computed: {
    markersSorted() {
      return this.markers
        .map(m => Object.assign(
          {},
          m,
          { distance: L.latLng(this.myLocation).distanceTo(L.latLng([m.lat, m.lng])) },
        ))
        .sort((a, b) => a.distance - b.distance);
    },
  },
  methods: {
    initMap() {
      this.map = L.map(this.$refs.map, { animate: true })
        .setView([50.411609, 4.444551], 10);
      L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}@2x.png', {
        attribution: `Map tiles by <a href="http://stamen.com">Stamen Design</a>,
          under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>.
          Data by <a href="http://openstreetmap.org">OpenStreetMap</a>,
          under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.`,
      }).addTo(this.map);
      this.lg.addTo(this.map);
      this.locationGroup.addTo(this.map);
      this.map.on('locationfound', this.onLocationFound);
    },
    getMarkers() {
      const json = 'https://carolor.org/map/api/partners/';
      return d3Json(json).then((data) => {
        this.markers = data.map((m) => {
          const marker = Object.assign(
            {},
            m,
            {
              lat: +m.lat,
              lng: +m.lng,
            },
            {
              popup: m.popup
                .replace(/http:\/\/carolor.org/g, 'https://carolor.org')
                .replace(/http:\/\/www.carolor.org/g, 'https://www.carolor.org')
                .replace(/(?:\r\n|\r|\n)+/g, '<br>'),
            },
          );
          const popupContent = document.createElement('div');

          const icon = L.icon({
            iconUrl: `https://carolor.org/wp-content/uploads/maps-marker-pro/icons/${marker.icon}`,
            shadowUrl: 'https://carolor.org/wp-content/plugins/maps-marker-pro/images/leaflet/marker-shadow.png',
            // size of the icon
            iconSize: [32, 37],
            // size of the shadow
            shadowSize: [41, 41],
            // point of the icon which will correspond to marker's location
            iconAnchor: [17, 36],
            // the same for the shadow
            shadowAnchor: [16, 46],
            // point from which the popup should open relative to the iconAnchor
            popupAnchor: [0, -30],
          });

          return Object.assign(
            {},
            marker,
            {
              marker: L.marker([m.lat, m.lng], { icon })
                .bindPopup(popupContent)
                .on({
                  popupopen: (e) => { this.onPopupOpen(e, marker); },
                  popupclose: this.onPopupClose,
                }),
            },
          );
        });
      });
    },
    onPopupOpen(e, marker) {
      const { popup } = e;
      const ComponentConstructor = Vue.extend(PopupContentComponent);
      this.popupComponent = new ComponentConstructor({
        propsData: { m: marker },
        parent: this,
      }).$mount();
      this.popupComponent.$on('showModal', (mm) => {
        this.showModal(mm);
      });
      popup.setContent(this.popupComponent.$el);
    },
    onPopupClose() {
      this.popupComponent.$destroy();
    },
    locate() {
      this.map.locate({ setView: true, maxZoom: 14 });
    },
    udpateMarkersOnMap() {
      this.lg.clearLayers();
      this.markers.forEach((m) => {
        this.lg.addLayer(m.marker);
      });
    },
    onLocationFound(e) {
      this.locationGroup.clearLayers();
      const radius = e.accuracy;
      this.myLocation = e.latlng;

      const locationMarker = L.marker(e.latlng)
        .bindPopup(`Vous êtes dans un rayon de ${radius} mètres de ce point`);

      this.locationGroup.addLayer(locationMarker);
      this.locationGroup.addLayer(L.circle(e.latlng, radius));
      locationMarker.openPopup();
      this.saveLocation(e.latlng, radius);
    },
    zoomTo(m) {
      this.map.setView(m.marker.getLatLng(), 14);
      m.marker.openPopup();
    },
    showModal(m) {
      this.mModal = m;
      this.$nextTick(() => {
        $('#exampleModal').modal('show');
      });
    },
    saveLocation(latlng, radius) {
      if (process.env.NODE_ENV === 'development') {
        return;
      }
      const params = {
        lat: latlng.lat,
        lng: latlng.lng,
        radius,
      };
      d3Json(
        'https://carolor.org/map/api/log/',
        {
          method: 'POST',
          headers: {
            'Content-type': 'application/json; charset=UTF-8',
          },
          body: JSON.stringify(params),
        },
      );
    },
  },
};
</script>

<style>
.sticky-top {
  top: 0.5em;
}
.map {
  width: 100%;
  height: 400px;
  border: 2px solid #000000;
}
.modal-body img {
  max-width: 300px;
  height: auto;
}
</style>
