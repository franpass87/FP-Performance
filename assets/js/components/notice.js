/**
 * WordPress Admin Notice Component
 *
 * @package FP\PerfSuite
 */

/**
 * Show WordPress admin notice
 *
 * @param {string} message - The message to display
 * @param {string} type - Notice type (success, error, warning, info)
 */
export function showNotice(message, type = 'success') {
	const notice     = document.createElement( 'div' );
	notice.className = `notice notice - ${type} is - dismissible`;
	notice.innerHTML = ` < p > ${message} < / p > `;

	const button     = document.createElement( 'button' );
	button.type      = 'button';
	button.className = 'notice-dismiss';
	button.innerHTML = '<span class="screen-reader-text">Dismiss this notice.</span>';
	button.addEventListener( 'click', () => notice.remove() );
	notice.appendChild( button );

	const wrap = document.querySelector( '.wrap h1' );
	if (wrap) {
		wrap.parentNode.insertBefore( notice, wrap.nextSibling );
	}
}