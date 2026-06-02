/* Qaiyo Testimonials - frontend (vanilla) */
(function () {
	'use strict';

	var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function initMarquees(root) {
		var marquees = root.querySelectorAll('.qt-marquee');
		marquees.forEach(function (m) {
			var track = m.querySelector('.qt-track');
			if (!track) return;
			var axis = m.getAttribute('data-axis') || 'x';
			var dir = m.getAttribute('data-direction') || 'left';
			var speed = parseInt(m.getAttribute('data-speed'), 10) || 30;

			var start = null;
			var loopSize = 0;
			var pos = 0;
			var paused = false;

			function measure() {
				if (axis === 'x') {
					loopSize = track.scrollWidth / 2;
				} else {
					loopSize = track.scrollHeight / 2;
				}
			}

			measure();
			if (loopSize <= 0) return;

			var sign = (dir === 'right' || dir === 'down') ? 1 : -1;
			if (sign === 1) {
				pos = -loopSize;
			}

			var pxPerMs = loopSize / (speed * 1000);

			function tick(ts) {
				if (start === null) start = ts;
				var dt = ts - start;
				start = ts;
				if (!paused) {
					pos += sign * pxPerMs * dt;
					if (sign === -1 && pos <= -loopSize) pos += loopSize;
					if (sign === 1 && pos >= 0) pos -= loopSize;
					if (axis === 'x') {
						track.style.transform = 'translate3d(' + pos + 'px,0,0)';
					} else {
						track.style.transform = 'translate3d(0,' + pos + 'px,0)';
					}
				}
				rafId = requestAnimationFrame(tick);
			}

			var rafId;
			if (reduced) {
				track.style.transform = '';
			} else {
				rafId = requestAnimationFrame(tick);
			}

			m.addEventListener('mouseenter', function () { paused = true; });
			m.addEventListener('mouseleave', function () { paused = false; });

			var resizeT;
			window.addEventListener('resize', function () {
				clearTimeout(resizeT);
				resizeT = setTimeout(function () {
					measure();
					if (sign === 1) pos = -loopSize;
				}, 200);
			});
		});
	}

	function initV5(root) {
		var v5s = root.querySelectorAll('.qt-v5');
		v5s.forEach(function (v) {
			var avatars = v.querySelectorAll('.qt-v5-avatar');
			var panels = v.querySelectorAll('.qt-v5-panel');
			if (!avatars.length || !panels.length) return;
			var speed = parseInt(v.getAttribute('data-speed'), 10) || 30;
			var interval = Math.max(1500, Math.round((speed * 1000) / avatars.length));
			var current = 0;
			var paused = false;
			var timer;

			function activate(i) {
				avatars.forEach(function (a, idx) {
					var on = idx === i;
					a.classList.toggle('is-active', on);
					a.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				panels.forEach(function (p, idx) {
					p.classList.toggle('is-active', idx === i);
				});
				current = i;
			}

			function next() {
				if (paused) return;
				activate((current + 1) % avatars.length);
			}

			avatars.forEach(function (a, idx) {
				a.addEventListener('click', function () {
					activate(idx);
					restart();
				});
			});

			function restart() {
				clearInterval(timer);
				timer = setInterval(next, interval);
			}

			v.addEventListener('mouseenter', function () { paused = true; });
			v.addEventListener('mouseleave', function () { paused = false; });

			if (!reduced) {
				restart();
			}
		});
	}

	function init() {
		var root = document;
		initMarquees(root);
		initV5(root);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
