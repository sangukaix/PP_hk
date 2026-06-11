// 지도를 보여줄 div 요소 찾기
var container = document.getElementById('map');

// 표시하고 싶은 위치 좌표
var position = new kakao.maps.LatLng(37.4849, 126.9306);

// 지도의 위치나 줌레벨 정도를 옵션으로 미리 지정
var options = {
  center: position,
  level: 3
};

// 지도 객체를 만들고 보여주기
var map = new kakao.maps.Map(container, options);

// ---------------------------------------------------

// 마커 이미지 주소
var imageSrc = './image/logomark.png';

// 마커 이미지 크기
var imageSize = new kakao.maps.Size(35, 35);

// 마커 이미지의 기준점 설정
// 로고형 마커라서 가운데를 실제 좌표에 맞춤
var imageOption = {
  offset: new kakao.maps.Point(17, 35)
};

// 마커 이미지 만들기
var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize, imageOption);

// 마커 생성
var marker = new kakao.maps.Marker({
  position: position,
  image: markerImage
});

// 마커를 지도 위에 표시
marker.setMap(map);


//------------------------------------//

// ---------------------------------------------------
// 현재 위치 지도 만들기

// 현재 위치 지도를 보여줄 div 요소 찾기
var currentMapContainer = document.getElementById('current_map');

// 현재 위치를 가져올 수 있는지 확인
if (navigator.geolocation) {

  // 현재 위치 가져오기
  navigator.geolocation.getCurrentPosition(function(position) {

    // 현재 위치의 위도, 경도
    var lat = position.coords.latitude;
    var lon = position.coords.longitude;

    // 현재 위치 좌표 만들기
    var currentPosition = new kakao.maps.LatLng(lat, lon);

    // 현재 위치 지도 옵션
    var currentMapOption = {
      center: currentPosition,
      level: 3
    };

    // 현재 위치 지도 만들기
    var currentMap = new kakao.maps.Map(currentMapContainer, currentMapOption);

    // 현재 위치에 마커 만들기
    var currentMarker = new kakao.maps.Marker({
      position: currentPosition
    });

    // 마커를 현재 위치 지도 위에 표시
    currentMarker.setMap(currentMap);

  }, function() {

    // 위치 정보를 가져오지 못했을 때
    alert('현재 위치를 가져올 수 없습니다. 위치 정보 사용을 허용해주세요.');
  });

} else {
  alert('이 브라우저에서는 위치 정보를 사용할 수 없습니다.');
}

// ---------------------------------------------------
// 여러 개 마커 제어 지도 만들기

// 마커 제어 지도를 보여줄 div 요소 찾기
var markerMapContainer = document.getElementById('marker_map');

// 마커 제어 지도 중심 좌표
var markerMapPosition = new kakao.maps.LatLng(37.4849, 126.9306);

// 마커 제어 지도 옵션
var markerMapOption = {
  center: markerMapPosition,
  level: 3
};

// 마커 제어 지도 만들기
var markerMap = new kakao.maps.Map(markerMapContainer, markerMapOption);

// 지도에 표시된 마커들을 저장할 배열
var controlMarkers = [];

// 처음 시작할 때 기본 마커 하나 표시
addControlMarker(markerMapPosition);

// 지도를 클릭했을 때 클릭한 위치에 마커 추가
kakao.maps.event.addListener(markerMap, 'click', function(mouseEvent) {

  // 클릭한 위치의 좌표
  var clickPosition = mouseEvent.latLng;

  // 클릭한 위치에 마커 추가
  addControlMarker(clickPosition);

});

// 마커를 생성하고 지도 위에 표시하는 함수
function addControlMarker(position) {

  // 마커 생성
  var marker = new kakao.maps.Marker({
    position: position
  });

  // 마커를 지도 위에 표시
  marker.setMap(markerMap);

  // 생성된 마커를 배열에 추가
  controlMarkers.push(marker);
}

// 배열에 있는 마커들을 지도에 표시하거나 감추는 함수
function setControlMarkers(map) {
  for (var i = 0; i < controlMarkers.length; i++) {
    controlMarkers[i].setMap(map);
  }
}

// 마커 보이기 버튼을 눌렀을 때 실행
function showControlMarkers() {
  setControlMarkers(markerMap);
}

// 마커 감추기 버튼을 눌렀을 때 실행
function hideControlMarkers() {
  setControlMarkers(null);
}