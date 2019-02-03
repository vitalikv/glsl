<!DOCTYPE html>
<html>
<head>
	<meta charset=utf-8>
	<title>Plan</title>
	<style>
		body { margin: 0; background:#ffffff; }
		canvas { width: 100%; height: 100%; background:#ffffff; }
	</style>
</head>
<body>
<script src="/js/three.min.js"></script>




<script id="vertexShader" type="x-shader/x-vertex">
varying vec3 vNormal;

void main() {

  // set the vNormal value with
  // the attribute value passed
  // in by Three.js
  vNormal = normal;

  gl_Position = projectionMatrix *
                modelViewMatrix *
                vec4(position, 1.0);
}
</script>


<script id="fragmentShader2" type="x-shader/x-fragment">
	uniform vec2 resolution;// Здесь сначала должны быть объявлены uniform-переменные
	uniform sampler2D texture;
	
	void main() 
	{
		// Теперь можно нормализовать координату
		vec2 pos = gl_FragCoord.xy / resolution.xy;
		// И создать градиент!
		gl_FragColor = vec4(1.0,pos.x,pos.y,1.0);
		//gl_FragColor = texture2D(texture,pos);
	}
</script>

<script id="fragmentShader" type="x-shader/x-fragment">
#ifdef GL_ES
precision mediump float;
#endif

uniform vec2 u_resolution;

uniform float u_time;


vec2 mirrorTile(vec2 _st, float _zoom){
    _st *= _zoom;
    if (fract(_st.y * 0.5) > 0.5){
        _st.x = _st.x+0.5;
        _st.y = 1.0-_st.y;
    }
    return fract(_st);
}

float fillY(vec2 _st, float _pct,float _antia){
  return  smoothstep( _pct-_antia, _pct, _st.y);
}

void main(){
  vec2 st = gl_FragCoord.xy/u_resolution.xy;
  vec3 color = vec3(0.0);

  st = mirrorTile(st*vec2(1.,2.),5.);
  float x = st.x*2.;
  float a = floor(1.+sin(x*3.14));
  float b = floor(1.+sin((x+1.)*3.14));
  float f = fract(x);

  color = vec3( fillY(st,mix(a,b,f),0.01) );

  gl_FragColor = vec4( color, 1.0 );
}
</script>







<script>

        var scene;
        var camera;
        var renderer;
console.log(THREE.ShaderLib);
        function scene_setup(){
            //This is all code needed to set up a basic ThreeJS scene

            //First we initialize the scene and our camera
            scene = new THREE.Scene();
			scene.background = new THREE.Color( 0xffffff );
            camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 );

            //We create the WebGL renderer and add it to the document
            renderer = new THREE.WebGLRenderer();
            renderer.setSize( window.innerWidth, window.innerHeight );
            document.body.appendChild( renderer.domElement );
        }

        scene_setup();

        //Add your code here!
        var geometry = new THREE.BoxGeometry( 1, 1, 1 );
        var material = new THREE.MeshBasicMaterial( { color: 0x00ff00} );//We make it green
		
var uniforms = {

		u_time: { type: 'f', value: 0.2 },
		u_resolution: { type:'v2', value:new THREE.Vector2(window.innerWidth,window.innerHeight) },
		texture: { type:'t', value: new THREE.TextureLoader().load('img/1.jpg') }

	}		
		
		
var material = new THREE.ShaderMaterial( {

	uniforms: uniforms,

	vertexShader: document.getElementById( 'vertexShader' ).textContent,

	fragmentShader: document.getElementById( 'fragmentShader' ).textContent

} );		
		
		
		
		
		
		
        var cube = new THREE.Mesh( geometry, material );
        //Add it to the screen
        scene.add( cube );
        cube.position.z = -3;//Shift the cube back so we can see it
        

        //Render everything!
        function render() {
      cube.rotation.y += 0.05;

	
            requestAnimationFrame( render );
            renderer.render( scene, camera );
        }
        render();
</script>
</body>
</html>

