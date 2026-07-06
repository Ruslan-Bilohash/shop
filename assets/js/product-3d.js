import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

(function () {
    'use strict';

    var viewers = document.querySelectorAll('[data-sh-product-3d]');
    if (!viewers.length) return;

    function parseColor(value, fallback) {
        try {
            return new THREE.Color(value || fallback);
        } catch (e) {
            return new THREE.Color(fallback);
        }
    }

    function addMesh(group, geometry, color, position, rotation, scale) {
        var mat = new THREE.MeshStandardMaterial({
            color: color,
            metalness: 0.35,
            roughness: 0.45,
        });
        var mesh = new THREE.Mesh(geometry, mat);
        if (position) mesh.position.copy(position);
        if (rotation) mesh.rotation.set(rotation.x || 0, rotation.y || 0, rotation.z || 0);
        if (scale) {
            if (typeof scale === 'number') mesh.scale.setScalar(scale);
            else mesh.scale.set(scale.x || 1, scale.y || 1, scale.z || 1);
        }
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);
        return mesh;
    }

    function buildPreset(preset, color) {
        var group = new THREE.Group();
        var c = parseColor(color, '#2563eb');
        var accent = c.clone().offsetHSL(0, -0.05, 0.12);

        switch (preset) {
            case 'headphones': {
                var band = new THREE.TorusGeometry(0.72, 0.06, 16, 64, Math.PI);
                addMesh(group, band, accent, new THREE.Vector3(0, 0.35, 0), new THREE.Vector3(0, 0, 0));
                addMesh(group, new THREE.SphereGeometry(0.28, 32, 32), c, new THREE.Vector3(-0.62, 0, 0));
                addMesh(group, new THREE.SphereGeometry(0.28, 32, 32), c, new THREE.Vector3(0.62, 0, 0));
                addMesh(group, new THREE.CylinderGeometry(0.18, 0.18, 0.08, 32), accent, new THREE.Vector3(-0.62, 0, 0.12));
                addMesh(group, new THREE.CylinderGeometry(0.18, 0.18, 0.08, 32), accent, new THREE.Vector3(0.62, 0, 0.12));
                break;
            }
            case 'watch': {
                addMesh(group, new THREE.CylinderGeometry(0.42, 0.42, 0.14, 48), c, new THREE.Vector3(0, 0, 0));
                addMesh(group, new THREE.CylinderGeometry(0.34, 0.34, 0.02, 48), accent, new THREE.Vector3(0, 0.08, 0));
                var strap = new THREE.BoxGeometry(0.22, 1.05, 0.06);
                addMesh(group, strap, accent, new THREE.Vector3(0, 0.58, 0));
                addMesh(group, strap, accent, new THREE.Vector3(0, -0.58, 0));
                break;
            }
            case 'apparel': {
                addMesh(group, new THREE.BoxGeometry(0.95, 0.72, 0.38), c, new THREE.Vector3(0, 0.05, 0), null, 1);
                addMesh(group, new THREE.BoxGeometry(0.38, 0.28, 0.32), accent, new THREE.Vector3(-0.58, 0.42, 0));
                addMesh(group, new THREE.BoxGeometry(0.38, 0.28, 0.32), accent, new THREE.Vector3(0.58, 0.42, 0));
                addMesh(group, new THREE.BoxGeometry(0.42, 0.22, 0.28), accent, new THREE.Vector3(0, 0.52, 0.02));
                break;
            }
            case 'bag': {
                addMesh(group, new THREE.BoxGeometry(0.72, 0.55, 0.28), c, new THREE.Vector3(0, -0.05, 0));
                var flap = new THREE.BoxGeometry(0.74, 0.18, 0.3);
                addMesh(group, flap, accent, new THREE.Vector3(0, 0.28, 0.02), new THREE.Vector3(0.25, 0, 0));
                addMesh(group, new THREE.TorusGeometry(0.22, 0.03, 12, 32, Math.PI), accent, new THREE.Vector3(0, 0.48, 0), new THREE.Vector3(0, 0, Math.PI));
                break;
            }
            case 'mug': {
                addMesh(group, new THREE.CylinderGeometry(0.38, 0.34, 0.62, 40), c, new THREE.Vector3(0, -0.05, 0));
                var handle = new THREE.TorusGeometry(0.16, 0.04, 12, 24, Math.PI);
                addMesh(group, handle, accent, new THREE.Vector3(0.42, 0.02, 0), new THREE.Vector3(0, 0, -Math.PI / 2));
                break;
            }
            case 'mat': {
                addMesh(group, new THREE.BoxGeometry(1.1, 0.06, 0.72), c, new THREE.Vector3(0, 0, 0));
                addMesh(group, new THREE.BoxGeometry(0.92, 0.04, 0.58), accent, new THREE.Vector3(0, 0.05, 0));
                break;
            }
            default: {
                addMesh(group, new THREE.BoxGeometry(0.75, 0.75, 0.75), c, new THREE.Vector3(0, 0, 0), new THREE.Vector3(0.4, 0.6, 0.2));
                break;
            }
        }

        return group;
    }

    function fitObject(object, maxSize) {
        var box = new THREE.Box3().setFromObject(object);
        var size = box.getSize(new THREE.Vector3());
        var center = box.getCenter(new THREE.Vector3());
        object.position.sub(center);
        var maxDim = Math.max(size.x, size.y, size.z) || 1;
        var scale = maxSize / maxDim;
        object.scale.setScalar(scale);
    }

    function initViewer(el) {
        if (el.dataset.sh3dReady === '1') return;
        el.dataset.sh3dReady = '1';

        var width = el.clientWidth || 280;
        var height = el.clientHeight || 220;

        var scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf5f3ef);

        var camera = new THREE.PerspectiveCamera(42, width / height, 0.1, 100);
        camera.position.set(0, 0.35, 2.35);

        var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.setSize(width, height);
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        el.appendChild(renderer.domElement);

        var pmrem = new THREE.PMREMGenerator(renderer);
        scene.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.04).texture;
        pmrem.dispose();

        scene.add(new THREE.AmbientLight(0xffffff, 0.55));
        var key = new THREE.DirectionalLight(0xffffff, 1.1);
        key.position.set(2, 3, 4);
        scene.add(key);
        var fill = new THREE.DirectionalLight(0xdbeafe, 0.45);
        fill.position.set(-2, 1, -2);
        scene.add(fill);

        var preset = el.getAttribute('data-preset') || 'default';
        var color = el.getAttribute('data-color') || '#2563eb';
        var modelUrl = el.getAttribute('data-model') || '';
        var productGroup = new THREE.Group();
        scene.add(productGroup);

        var controls = new OrbitControls(camera, renderer.domElement);
        controls.enablePan = false;
        controls.enableDamping = true;
        controls.dampingFactor = 0.06;
        controls.minDistance = 1.2;
        controls.maxDistance = 4.5;
        controls.target.set(0, 0, 0);

        function mountObject(object) {
            productGroup.clear();
            productGroup.add(object);
            fitObject(productGroup, 1.35);
        }

        if (modelUrl) {
            var loader = new GLTFLoader();
            loader.load(modelUrl, function (gltf) {
                mountObject(gltf.scene);
            }, undefined, function () {
                mountObject(buildPreset(preset, color));
            });
        } else {
            mountObject(buildPreset(preset, color));
        }

        var animId = 0;
        var running = true;

        function animate() {
            if (!running) return;
            animId = requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }
        animate();

        function resize() {
            var w = el.clientWidth || width;
            var h = el.clientHeight || height;
            if (w < 10 || h < 10) return;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        }

        var ro = typeof ResizeObserver !== 'undefined'
            ? new ResizeObserver(resize)
            : null;
        if (ro) ro.observe(el);
        else window.addEventListener('resize', resize);

        el.addEventListener('sh-3d-dispose', function () {
            running = false;
            cancelAnimationFrame(animId);
            controls.dispose();
            renderer.dispose();
            if (ro) ro.disconnect();
            el.innerHTML = '';
            delete el.dataset.sh3dReady;
        });
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                initViewer(entry.target);
                io.unobserve(entry.target);
            });
        }, { rootMargin: '120px 0px' });
        viewers.forEach(function (el) { io.observe(el); });
    } else {
        viewers.forEach(initViewer);
    }
})();