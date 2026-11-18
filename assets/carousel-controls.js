console.info('[carousel-controls] script loaded');

function moveCarousel(button) {
    const track = button.closest('.team-section, .resound-section, .software-section')
        ?.querySelector('.team-track.scrolling, .resound-track.scrolling, .software-track.is-scrolling');
    const animations = track?.getAnimations() ?? [];
    const animation = animations.find((item) => item.playState !== 'idle');
    const direction = Number(button.dataset.carouselSpeed);

    console.info('[carousel-controls] button clicked', {
        direction,
        section: button.closest('section')?.id,
        trackFound: Boolean(track),
        animationsFound: animations.length,
        animationFound: Boolean(animation),
        playState: animation?.playState,
        playbackRate: animation?.playbackRate,
    });

    if (!track || !animation) {
        console.warn('[carousel-controls] cannot accelerate: track or animation missing');
        return;
    }

    const currentOffset = new DOMMatrixReadOnly(getComputedStyle(track).transform).m41;
    const targetOffset = currentOffset - direction * 400;
    const cycleDuration = Number(animation.effect.getTiming().duration);
    const cycleWidth = track.scrollWidth / 2;
    const from = currentOffset;
    const to = targetOffset;

    animation.pause();
    track.classList.add('is-manually-moving');
    console.info('[carousel-controls] moving 400px', { from, to });

    const move = track.animate(
        [
            { transform: `translate3d(${from}px, 0, 0)` },
            { transform: `translate3d(${to}px, 0, 0)` },
        ],
        { duration: 380, easing: 'cubic-bezier(.2,.75,.25,1)' },
    );

    move.onfinish = () => {
        track.classList.remove('is-manually-moving');
        animation.currentTime = (animation.currentTime ?? 0)
            + direction * cycleDuration * (400 / cycleWidth);
        move.cancel();
        syncCarouselPlayback(track);
    };
}

document.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-carousel-speed]') : null;
    if (button) moveCarousel(button);
});

let dragState = null;
let suppressCarouselClickUntil = 0;

function getTrackAnimation(track) {
    return track.getAnimations().find((animation) =>
        animation.effect?.target === track
        && animation.playState !== 'idle'
        && Number.isFinite(Number(animation.effect.getTiming().duration))
    ) ?? null;
}

function readTranslateX(element) {
    const transform = getComputedStyle(element).transform;
    return transform === 'none' ? 0 : new DOMMatrixReadOnly(transform).m41;
}

function syncCarouselPlayback(track) {
    if (!track) {
        return;
    }

    const animation = getTrackAnimation(track);
    if (!animation) {
        return;
    }

    const resoundShouldPause = track.matches('.resound-track')
        && (track.classList.contains('video-hovered') || track.classList.contains('video-playing'));
    const teamShouldPause = track.matches('.team-track') && track.matches(':hover');

    if (resoundShouldPause || teamShouldPause) {
        animation.pause();
    } else {
        animation.play();
    }
}

window.syncResoundCarouselPlayback = syncCarouselPlayback;

function syncTeamHover(event, entering) {
    if (!(event.target instanceof Element)) {
        return;
    }

    const track = event.target.closest('.team-track.scrolling');
    if (!track) {
        return;
    }

    const relatedTarget = event.relatedTarget;
    if (relatedTarget instanceof Node && track.contains(relatedTarget)) {
        return;
    }

    if (entering) {
        syncCarouselPlayback(track);
    } else {
        requestAnimationFrame(() => syncCarouselPlayback(track));
    }
}

document.addEventListener('pointerover', (event) => syncTeamHover(event, true));
document.addEventListener('pointerout', (event) => syncTeamHover(event, false));

document.addEventListener('pointerdown', (event) => {
    if (!event.isPrimary || (event.pointerType === 'mouse' && event.button !== 0)) {
        return;
    }

    const marquee = event.target instanceof Element
        ? event.target.closest('.team-marquee, .resound-marquee, .software-marquee')
        : null;
    const track = marquee?.querySelector('.team-track, .resound-track, .software-track');

    if (!marquee || !track || event.target.closest('a, button, audio, input, select, textarea, [data-no-carousel-drag]')) {
        return;
    }

    dragState = {
        pointerId: event.pointerId,
        marquee,
        track,
        startX: event.clientX,
        startY: event.clientY,
        lastX: event.clientX,
        startOffset: readTranslateX(track),
        animation: getTrackAnimation(track),
        startTime: null,
        cycleDuration: null,
        cycleWidth: null,
        dragging: false,
    };
}, true);

document.addEventListener('pointermove', (event) => {
    if (!dragState || event.pointerId !== dragState.pointerId) {
        return;
    }

    const totalDelta = event.clientX - dragState.startX;
    const verticalDelta = event.clientY - dragState.startY;
    if (!dragState.dragging) {
        if (Math.max(Math.abs(totalDelta), Math.abs(verticalDelta)) < 7) {
            return;
        }
        if (Math.abs(verticalDelta) > Math.abs(totalDelta)) {
            dragState = null;
        }
        if (!dragState) return;
    }

    if (!dragState.dragging) {
        dragState.dragging = true;
        dragState.marquee.classList.add('is-dragging');
        try {
            dragState.marquee.setPointerCapture(event.pointerId);
        } catch (_) {
            // Document-level pointer listeners remain as a fallback.
        }
        dragState.animation?.pause();
        dragState.startTime = Number(dragState.animation?.currentTime ?? 0);
        dragState.cycleDuration = Number(dragState.animation?.effect.getTiming().duration ?? 0);
        dragState.cycleWidth = dragState.track.scrollWidth / (dragState.animation ? 2 : 1);
    }

    event.preventDefault();
    dragState.lastX = event.clientX;

    if (dragState.animation && dragState.cycleWidth > 0) {
        const duration = dragState.cycleDuration;
        const timeDelta = -totalDelta * duration / dragState.cycleWidth;
        dragState.animation.currentTime = ((dragState.startTime + timeDelta) % duration + duration) % duration;
        return;
    }

    const maxOffset = Math.max(0, dragState.track.scrollWidth - dragState.marquee.clientWidth);
    const nextOffset = Math.min(0, Math.max(-maxOffset, dragState.startOffset + totalDelta));
    dragState.track.style.transform = `translate3d(${nextOffset}px, 0, 0)`;
}, { capture: true, passive: false });

document.addEventListener('dragstart', (event) => {
    if (event.target instanceof Element && event.target.closest('.team-marquee, .resound-marquee, .software-marquee')) {
        event.preventDefault();
    }
}, true);

function finishCarouselDrag(event) {
    if (!dragState || event.pointerId !== dragState.pointerId) {
        return;
    }

    const state = dragState;
    dragState = null;
    state.marquee.classList.remove('is-dragging');

    if (state.dragging) {
        suppressCarouselClickUntil = Date.now() + 350;
        if (state.animation) {
            syncCarouselPlayback(state.track);
        }
    }
}

document.addEventListener('pointerup', finishCarouselDrag, true);
document.addEventListener('pointercancel', finishCarouselDrag, true);

document.addEventListener('click', (event) => {
    if (Date.now() > suppressCarouselClickUntil || !(event.target instanceof Element)) {
        return;
    }

    if (event.target.closest('.team-marquee, .resound-marquee, .software-marquee')) {
        event.preventDefault();
        event.stopImmediatePropagation();
        suppressCarouselClickUntil = 0;
    }
}, true);
