<template>
  <div class="h-screen w-full">
    <div id="map" class="h-full w-full"></div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import Echo from 'laravel-echo';

// Fix for marker icons
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const map = ref(null);
const marker = ref(null);
const polyline = ref(null);
const positions = ref([]);

onMounted(() => {
  // Initialize map
  map.value = L.map('map').setView([35.6892, 51.3890], 13);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
  }).addTo(map.value);

  // Initialize polyline for tracking
  polyline.value = L.polyline([], {
    color: 'red',
    weight: 3,
    opacity: 0.7
  }).addTo(map.value);

  // Listen for real-time GPS updates
  window.Echo.channel('gps-tracking')
    .listen('GpsLocationEvent', (e) => {
      updateMap({
        latitude: e.latitude,
        longitude: e.longitude
      });
    });

  // Initial data fetch
  fetchGpsData();
});

const fetchGpsData = async () => {
  try {
    const response = await fetch('/api/gps-data');
    const data = await response.json();

    if (data.success && data.data) {
      updateMap(data.data);
    }
  } catch (error) {
    console.error('Error fetching GPS data:', error);
  }
};

const updateMap = (gpsData) => {
  const newPosition = [gpsData.latitude, gpsData.longitude];
  positions.value.push(newPosition);

  // Update marker position
  if (!marker.value) {
    marker.value = L.marker(newPosition).addTo(map.value);
  } else {
    marker.value.setLatLng(newPosition);
  }

  // Update polyline
  polyline.value.setLatLngs(positions.value);

  // Center map on new position
  map.value.setView(newPosition);
};
</script>

<style>
@import 'leaflet/dist/leaflet.css';
</style>
