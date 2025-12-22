/**
 * FP Performance Suite - Risk Tooltip System
 * 
 * Sistema tooltip completamente ricostruito da zero
 * Semplice, diretto, senza dipendenze esterne
 * 
 * @package FP\PerfSuite
 * @version 2.0.1 - Cache busting migliorato 2025-12-21-18:36
 */

'use strict';

/**
 * Mostra e posiziona il tooltip
 */
function showTooltip(indicator, tooltip) {
	// 1. Prepara tooltip per misurazione (nascosto ma renderizzato)
	// IMPORTANTE: Rimuovi TUTTI gli stili inline che potrebbero limitare l'altezza
	tooltip.style.cssText = '';
	
	// 2. Applica stili base per misurazione (senza limiti di altezza)
	// IMPORTANTE: Usa visibility: hidden invece di display: none per permettere misurazione
	tooltip.style.cssText = `
		position: fixed !important;
		visibility: hidden !important;
		opacity: 0 !important;
		top: 0 !important;
		left: 0 !important;
		display: block !important;
		height: auto !important;
		min-height: auto !important;
		max-height: none !important;
		width: auto !important;
		min-width: 350px !important;
		max-width: ${actualTooltipWidth}px !important;
		overflow: visible !important;
		z-index: -1 !important;
	`;
	
	// 3. Rimuovi anche limiti di altezza da elementi interni
	const contentElement = tooltip.querySelector('.fp-ps-risk-tooltip-content');
	if (contentElement) {
		contentElement.style.height = 'auto';
		contentElement.style.minHeight = 'auto';
		contentElement.style.maxHeight = 'none';
		contentElement.style.overflow = 'visible';
	}
	const textElements = tooltip.querySelectorAll('.fp-ps-risk-tooltip-text, .fp-ps-risk-tooltip-section');
	textElements.forEach(el => {
		el.style.height = 'auto';
		el.style.minHeight = 'auto';
		el.style.maxHeight = 'none';
		el.style.overflow = 'visible';
	});
	
	// 4. Forza layout per ottenere dimensioni reali
	// Usa requestAnimationFrame per assicurare che il browser abbia renderizzato
	requestAnimationFrame(() => {
		requestAnimationFrame(() => {
			void tooltip.offsetHeight;
		});
	});
	
	// 5. Ottieni dimensioni reali per il posizionamento
	// Forza un reflow completo prima di misurare
	void tooltip.offsetHeight;
	void tooltip.scrollHeight;
	
	const indicatorRect = indicator.getBoundingClientRect();
	const tooltipRect = tooltip.getBoundingClientRect();
	const viewport = {
		width: window.innerWidth,
		height: window.innerHeight
	};
	
	// IMPORTANTE: Usa scrollHeight per ottenere l'altezza reale del contenuto
	const tooltipScrollHeight = tooltip.scrollHeight;
	const tooltipWidth = tooltipRect.width;
	
	const gap = 10;
	const margin = 10;
	
	// 6. Determina l'altezza massima disponibile nella viewport
	const maxAvailableHeight = viewport.height - (margin * 2);
	const tooltipNaturalHeight = tooltipScrollHeight;
	
	// Se il tooltip è troppo alto, limitalo e aggiungi scroll
	let tooltipHeight = tooltipNaturalHeight;
	let needsScroll = false;
	if (tooltipNaturalHeight > maxAvailableHeight) {
		tooltipHeight = maxAvailableHeight;
		needsScroll = true;
	}
	
	// 7. Calcola posizione verticale - usa l'altezza corretta
	const spaceAbove = indicatorRect.top;
	const spaceBelow = viewport.height - indicatorRect.bottom;
	
	let top;
	let arrowClass = '';
	
	// Prova prima sopra, poi sotto
	if (spaceAbove >= tooltipHeight + gap + margin) {
		// C'è spazio sopra, posiziona sopra
		top = indicatorRect.top - tooltipHeight - gap;
		arrowClass = '';
		// Verifica che non esca dalla viewport
		if (top < margin) {
			top = margin;
		}
	} else if (spaceBelow >= tooltipHeight + gap + margin) {
		// C'è spazio sotto, posiziona sotto
		top = indicatorRect.bottom + gap;
		arrowClass = 'arrow-top';
		// Verifica che non esca dalla viewport
		if (top + tooltipHeight > viewport.height - margin) {
			top = viewport.height - tooltipHeight - margin;
		}
	} else {
		// Non c'è spazio né sopra né sotto, posiziona dove c'è più spazio
		if (spaceAbove > spaceBelow) {
			// Più spazio sopra, posiziona sopra (limitato)
			top = margin;
			arrowClass = '';
			// Limita l'altezza allo spazio disponibile
			tooltipHeight = Math.min(tooltipHeight, spaceAbove - gap - margin);
			needsScroll = true;
		} else {
			// Più spazio sotto, posiziona sotto (limitato)
			top = indicatorRect.bottom + gap;
			arrowClass = 'arrow-top';
			// Verifica che non esca dalla viewport
			if (top + tooltipHeight > viewport.height - margin) {
				top = viewport.height - tooltipHeight - margin;
			}
			// Limita l'altezza allo spazio disponibile
			tooltipHeight = Math.min(tooltipHeight, spaceBelow - gap - margin);
			needsScroll = true;
		}
	}
	
	// 8. Calcola posizione orizzontale (centrato)
	let left = indicatorRect.left + (indicatorRect.width / 2) - (tooltipWidth / 2);
	
	// 9. Aggiusta bordi viewport orizzontali
	if (left < margin) {
		// Tooltip esce a sinistra, posiziona a sinistra con margine
		left = margin;
	} else if (left + tooltipWidth > viewport.width - margin) {
		// Tooltip esce a destra, posiziona a destra con margine
		left = viewport.width - tooltipWidth - margin;
		if (left < margin) {
			// Tooltip troppo largo, riduci la larghezza
			const maxWidth = viewport.width - (margin * 2);
			tooltip.style.maxWidth = maxWidth + 'px';
			// Forza reflow per ottenere nuova larghezza
			void tooltip.offsetWidth;
			const newTooltipWidth = tooltip.getBoundingClientRect().width;
			// Ricalcola posizione centrata
			left = indicatorRect.left + (indicatorRect.width / 2) - (newTooltipWidth / 2);
			// Verifica di nuovo i bordi
			if (left < margin) {
				left = margin;
			}
			if (left + newTooltipWidth > viewport.width - margin) {
				left = viewport.width - newTooltipWidth - margin;
			}
		}
	}
	
	// 10. Verifica finale che il tooltip non esca dalla viewport verticalmente
	if (top < margin) {
		top = margin;
	}
	if (top + tooltipHeight > viewport.height - margin) {
		top = viewport.height - tooltipHeight - margin;
		if (top < margin) {
			top = margin;
			// Se ancora esce, limita l'altezza
			tooltipHeight = viewport.height - (margin * 2);
			needsScroll = true;
		}
	}
	
	// 11. Applica stili finali con altezza corretta
	tooltip.style.cssText = `
		position: fixed !important;
		top: ${top}px !important;
		left: ${left}px !important;
		z-index: 999999999 !important;
		display: block !important;
		opacity: 1 !important;
		visibility: visible !important;
		pointer-events: auto !important;
		background: #1e293b !important;
		background-color: #1e293b !important;
		border: 2px solid rgba(255, 255, 255, 0.15) !important;
		border-radius: 8px !important;
		box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
		color: #ffffff !important;
		font-size: 14px !important;
		line-height: 1.75 !important;
		padding: 18px 22px !important;
		box-sizing: border-box !important;
		min-width: 350px !important;
		max-width: ${tooltipWidth}px !important;
		width: auto !important;
		height: ${tooltipHeight}px !important;
		min-height: ${tooltipHeight}px !important;
		max-height: ${needsScroll ? tooltipHeight + 'px' : 'none'} !important;
		overflow: ${needsScroll ? 'auto' : 'visible'} !important;
		overflow-y: ${needsScroll ? 'auto' : 'visible'} !important;
	`;
	
	// 12. Assicura che il contenuto gestisca correttamente lo scroll se necessario
	if (contentElement) {
		if (needsScroll) {
			contentElement.style.overflowY = 'auto';
			contentElement.style.maxHeight = '100%';
		} else {
			contentElement.style.height = '';
			contentElement.style.minHeight = '';
			contentElement.style.maxHeight = '';
			contentElement.style.overflow = 'visible';
		}
	}
	
	// 13. Gestione freccia
	const arrowLeft = indicatorRect.left + (indicatorRect.width / 2) - left;
	tooltip.style.setProperty('--arrow-left', `${arrowLeft}px`);
	
	// Rimuovi classe arrow-top se presente, poi aggiungi se necessario
	tooltip.classList.remove('arrow-top');
	if (arrowClass) {
		tooltip.classList.add(arrowClass);
	}
	
	// 14. Mostra tooltip
	tooltip.classList.add('is-visible');
	
	// 15. Forza reflow finale e verifica posizione
	void tooltip.offsetHeight;
	
	// 16. Verifica finale che il tooltip sia dentro la viewport
	// Usa requestAnimationFrame per assicurarsi che il browser abbia renderizzato completamente
	requestAnimationFrame(() => {
		requestAnimationFrame(() => {
			const finalRect = tooltip.getBoundingClientRect();
			const finalViewport = {
				width: window.innerWidth,
				height: window.innerHeight
			};
			
			let needsReposition = false;
			let newLeft = left;
			let newTop = top;
			
			// Correggi posizione orizzontale
			if (finalRect.left < margin) {
				newLeft = margin;
				needsReposition = true;
			}
			if (finalRect.right > finalViewport.width - margin) {
				newLeft = finalViewport.width - finalRect.width - margin;
				if (newLeft < margin) {
					newLeft = margin;
				}
				needsReposition = true;
			}
			
			// Correggi posizione verticale
			if (finalRect.top < margin) {
				newTop = margin;
				needsReposition = true;
			}
			if (finalRect.bottom > finalViewport.height - margin) {
				newTop = finalViewport.height - finalRect.height - margin;
				if (newTop < margin) {
					newTop = margin;
				}
				needsReposition = true;
			}
			
			// Applica correzione se necessaria
			if (needsReposition) {
				tooltip.style.left = newLeft + 'px';
				tooltip.style.top = newTop + 'px';
				
				// Ricalcola posizione freccia
				const indicatorRect = indicator.getBoundingClientRect();
				const newArrowLeft = indicatorRect.left + (indicatorRect.width / 2) - newLeft;
				tooltip.style.setProperty('--arrow-left', `${newArrowLeft}px`);
			}
		});
	});
}

/**
 * Nasconde il tooltip
 */
function hideTooltip(tooltip) {
	tooltip.classList.remove('is-visible');
	// NON usare display: none perché impedisce la misurazione corretta
	// Rimuovi solo gli stili di visibilità, mantieni display: block per misurazione
	tooltip.style.opacity = '0';
	tooltip.style.visibility = 'hidden';
	tooltip.style.pointerEvents = 'none';
}

/**
 * Inizializza un singolo indicatore
 */
function initIndicator(indicator) {
	// Verifica validità
	if (!indicator || !indicator.nodeType || indicator.nodeType !== 1) {
		console.warn('[Tooltip] Indicatore non valido:', indicator);
		return;
	}
	
	// Verifica se già inizializzato
	if (indicator.dataset.tooltipInitialized === 'true') {
		return;
	}
	
	// Trova tooltip
	const tooltip = indicator.querySelector('.fp-ps-risk-tooltip');
	if (!tooltip) {
		console.warn('[Tooltip] Tooltip non trovato per indicatore:', indicator);
		return;
	}
	
	// Marca come inizializzato
	indicator.dataset.tooltipInitialized = 'true';
	console.log('[Tooltip] Indicatore inizializzato:', indicator);
	
	// Event listeners con verifica coordinate mouse
	indicator.addEventListener('mouseenter', function(e) {
		// Verifica che il mouse sia effettivamente sopra l'indicatore
		const rect = indicator.getBoundingClientRect();
		const x = e.clientX;
		const y = e.clientY;
		
		// Controlla se il mouse è dentro i bounds dell'indicatore (con piccolo margine per facilità d'uso)
		const margin = 5; // 5px di tolleranza
		if (x >= rect.left - margin && x <= rect.right + margin && 
		    y >= rect.top - margin && y <= rect.bottom + margin) {
			showTooltip(indicator, tooltip);
		}
	});
	
	indicator.addEventListener('mouseleave', function(e) {
		// Verifica che il mouse sia effettivamente uscito dall'indicatore
		const rect = indicator.getBoundingClientRect();
		const x = e.clientX;
		const y = e.clientY;
		
		// Se il mouse è ancora dentro i bounds (con margine), non nascondere
		const margin = 5;
		if (x >= rect.left - margin && x <= rect.right + margin && 
		    y >= rect.top - margin && y <= rect.bottom + margin) {
			return; // Non nascondere, il mouse è ancora sopra
		}
		
		hideTooltip(tooltip);
	});
	
	indicator.addEventListener('focus', function() {
		showTooltip(indicator, tooltip);
	});
	
	indicator.addEventListener('blur', function() {
		hideTooltip(tooltip);
	});
	
	// Reposition su resize/scroll
	let resizeTimeout;
	let scrollTimeout;
	
	const reposition = function() {
		if (tooltip.classList.contains('is-visible')) {
			showTooltip(indicator, tooltip);
		}
	};
	
	window.addEventListener('resize', function() {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(reposition, 50);
	});
	
	window.addEventListener('scroll', function() {
		clearTimeout(scrollTimeout);
		scrollTimeout = setTimeout(reposition, 50);
	}, { passive: true });
}

/**
 * Inizializza tutti i tooltip
 */
export function initTooltips() {
	console.log('[Tooltip] initTooltips chiamato');
	
	// Trova tutti gli indicatori
	const indicators = document.querySelectorAll('.fp-ps-risk-indicator:not([data-tooltip-initialized])');
	console.log('[Tooltip] Indicatori trovati:', indicators.length);
	
	indicators.forEach(function(indicator) {
		try {
			initIndicator(indicator);
		} catch (e) {
			console.error('[Tooltip] Errore inizializzando indicatore:', e);
		}
	});
	
	const initialized = document.querySelectorAll('.fp-ps-risk-indicator[data-tooltip-initialized]').length;
	console.log('[Tooltip] Indicatori inizializzati:', initialized, 'di', indicators.length);
	
	// MutationObserver per elementi dinamici
	if (typeof MutationObserver !== 'undefined') {
		const observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				mutation.addedNodes.forEach(function(node) {
					if (node.nodeType === 1) {
						// Verifica se il nodo stesso è un indicatore
						if (node.classList && node.classList.contains('fp-ps-risk-indicator')) {
							initIndicator(node);
						}
						// Verifica indicatori figli
						const childIndicators = node.querySelectorAll && node.querySelectorAll('.fp-ps-risk-indicator:not([data-tooltip-initialized])');
						if (childIndicators && childIndicators.length > 0) {
							childIndicators.forEach(function(indicator) {
								initIndicator(indicator);
							});
						}
					}
				});
			});
		});
		
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}
}

// NOTA: L'inizializzazione viene gestita da main.js
// Non auto-inizializzare qui per evitare doppie inizializzazioni
