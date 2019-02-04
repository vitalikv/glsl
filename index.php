<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Sample 3</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<style>
			html, body {background-color: #000; margin: 0px; padding: 0px; overflow: hidden; }
		</style>

	</head>
	<body>
		<div id="container"></div>

		<script src="js/three.min.js"></script>
		<script src="https://threejs.org/examples/js/controls/OrbitControls.js"></script>
		<script src="http://appogeddon.com/sample3/js/Tween.min.js"></script>
		<script src="http://appogeddon.com/sample3/js/Projector.js"></script>
		<script src="js/jquery.js"></script>
		<script src="http://appogeddon.com/sample3/js/jquery.mousewheel.min.js"></script>		

		<script id="2d-fragment-shader" type="x-shader/x-fragment">
			uniform samplerCube tCube0;
			uniform samplerCube tCube1;
			uniform vec3 tCubePosition0;
			uniform vec3 tCubePosition1;
			uniform float scale0;
			uniform float scale1;
			uniform float tFlip;
			uniform float opacity;
			uniform float mixAlpha;
			varying vec3 vWorldPosition;
			#include <common>

			void main() {
				vec3 vWorldPositionScaled0 = mix(vWorldPosition, tCubePosition0, scale0);
				// Also tried:
				//vec3 vWorldPositionScaled0 = normalize(vWorldPosition) + scale0 * tCubePosition0;
				//vec3 vWorldPositionScaled0 = vWorldPosition + scale0 * tCubePosition0;
				vec3 vWorldPositionScaled1 = mix(vWorldPosition, tCubePosition1, scale1); //vWorldPosition + scale1 * tCubePosition1;
				vec4 tex0, tex1;
	     		tex0 = textureCube(tCube0, vec3( tFlip * vWorldPositionScaled0.x, vWorldPositionScaled0.yz ));
	     		tex1 = textureCube(tCube1, vec3( tFlip * vWorldPositionScaled1.x, vWorldPositionScaled1.yz ));
				gl_FragColor = mix(tex0, tex1, mixAlpha);
			}
		</script>
		<script id="2d-vertex-shader" type="x-shader/x-vertex">
			varying vec3 vWorldPosition;
			#include <common>
			void main() {
				vWorldPosition = transformDirection( position, modelMatrix );
				#include <begin_vertex>
				#include <project_vertex>
			}
		</script>

		
		<script>
		var camera, scene,
			controls,
			renderer,
			FOV = 75, NEAR = 0.1, FAR = 100, fovMAX = 95, fovMIN = 5,
			mouse, 
			raycaster, 
			projector,
			isTransitioning = false;

		init();
		animate();

		function init() {

			var container = document.getElementById( 'container' );

			renderer = new THREE.WebGLRenderer();
			renderer.setPixelRatio( window.devicePixelRatio );
			renderer.setSize( window.innerWidth, window.innerHeight );
			renderer.autoClear = false;
			container.appendChild( renderer.domElement );

			// Set Scene & Camera
			scene = getScene();
			camera = getCamera();

			// OrbitControls
			controls = new THREE.OrbitControls( camera );
			controls.enableZoom = false;
			controls.enablePan = false;

			// Raycaster TODO: needed?
			raycaster = new THREE.Raycaster();
			mouse = new THREE.Vector2();

			// Set listeners
			window.addEventListener( 	'resize', 	onWindowResize, 	false );
			window.addEventListener( 	'mousemove',onMouseMove, 		false );
			
			$(document).mousewheel(function (event, delta, deltaX, deltaY) {
			    event.preventDefault();
					if (deltaY < 0) {
				  	console.log('scrolling up');
						goToNextScene(true);
				  }else if (deltaY > 0) {
				    console.log('scrolling down');
						goToNextScene(false);
				  }
			});

		}

		// THIS IS TRANSITIONING ROUTINE:
		function goToNextScene(isDirUp){
				if(isTransitioning == false){
						console.log('goToNextScene',isDirUp);
						isTransitioning = true;
						// set position
						camera.updateMatrixWorld();
						uniforms['tCubePosition0'].value = new THREE.Vector3();
						uniforms['tCubePosition1'].value = new THREE.Vector3();
						materials.needsUpdate = true;
						// set alpha
						var val = isDirUp ? 1: 0;
						var tween = new TWEEN.Tween(uniforms['mixAlpha'])
								.to({value: val}, 500)
								.onStart(function(){
								  new TWEEN.Tween(uniforms['scale0']).to({ value:0.2 }, 500).start();
								})
								.easing(TWEEN.Easing.Cubic.In) //http://sole.github.io/tween.js/examples/03_graphs.html
								.start();
						setTimeout(function(){//TODO:better?
								isTransitioning = false;
						},500);
				}
		}

		function getCamera(){
				var _camera = new THREE.PerspectiveCamera( FOV, (window.innerWidth / window.innerHeight), NEAR, FAR );
				_camera.position.z = 0.01; // we can also camera.position.set(0,6,0);
				return _camera;
		}

		function getScene() {

			var _scene = new THREE.Scene();

			
			fragmentShader = document.getElementById('2d-fragment-shader').text;
			vertexShader = document.getElementById('2d-vertex-shader').text;
			uniforms = { mixAlpha: {type: "f", value: 1},
									 opacity: {type: "f", value: 1},
									 scale0: {type: "f", value: 0}, 
									 scale1: {type: "f", value: 0},
									 tCubePosition0: {type: "v3", value: new THREE.Vector3()},
               						 tCubePosition1: {type: "v3", value: new THREE.Vector3()},
               						 tCube0: {type: "t", value: 'CubeTexture'}, tCube1: {type: "t", value: 'CubeTexture'},
									 tFlip: {type: "f", value: -1} }
			uniforms[ "tCube0" ].value = getTextureCube(1);
			uniforms[ "tCube1" ].value = getTextureCube(0);
			
			var geometry = new THREE.BoxGeometry( 1, 1, 1 );//new THREE.CubeGeometry( 1, 1, 1 )
			materials = new THREE.ShaderMaterial({
					fragmentShader: fragmentShader,
					vertexShader: vertexShader,
					uniforms: uniforms,
					side: THREE.BackSide,
					transparent: true
			});

			var skyBox = new THREE.Mesh( geometry, materials );
			_scene.add( skyBox );

			return _scene;
		}


		function getTextureCube(ind){//testing THREE.CubeTextureLoader
				
				var loader = new THREE.CubeTextureLoader();

				
var path = "img/";
var format = '.jpg';
if(ind==1){format = '2.jpg';}
var urls = [ 
path + 'left' + format, path + 'right' + format,
path + 'up' + format, path + 'down' + format,
path + 'back' + format, path + 'front' + format,
];
				
				//loader.setCrossOrigin('anonymous');
				//loader.setCrossOrigin('');
				var textureCube = loader.load(urls, function(texture){
							console.log('done loading texture', texture);
					}, function ( xhr ) {
							console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
					}, function ( xhr ) {
							console.log( 'An error happened' );
					});
				return textureCube;
		}

		function animate() {
			controls.update();
			renderer.render( scene, camera );
			requestAnimationFrame( animate );
			TWEEN.update();
		}

		function addLight(scene){//TODO: add light
			
		}


		// ==========================================
		// Event Handlers
		// ==========================================

		function onWindowResize() {
			camera.aspect = window.innerWidth / window.innerHeight;
			camera.updateProjectionMatrix();
			renderer.setSize( window.innerWidth, window.innerHeight );
		}



		function onMouseMove( event ) {
			event.preventDefault();
			// calculate mouse position in normalized device coordinates
			// (-1 to +1) for both components
			mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
			mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

			// //https://stackoverflow.com/questions/29329634/detect-mouse-click-hover-event-on-sides-of-cube-in-three-js
			var mouseVector = new THREE.Vector3(
		        ( event.clientX / window.innerWidth ) * 2 - 1,
		      - ( event.clientY / window.innerHeight ) * 2 + 1,
		        1 );
	    mouseVector.unproject( camera ); //old //projector.unprojectVector( mouseVector, camera );
	    var raycaster = new THREE.Raycaster( camera.position, mouseVector.sub( camera.position ).normalize() );

	    // create an array containing all objects in the scene with which the ray intersects
	    var intersects = raycaster.intersectObjects( scene.children );
	    //console.log(intersects);
	    if (intersects.length>0){
	        //console.log("Intersected object:", intersects);
	        var index = Math.floor( intersects[0].faceIndex / 2 );
					Map.currentCube = index;
			
	    }

		}


		

		</script>

	</body>
</html>
