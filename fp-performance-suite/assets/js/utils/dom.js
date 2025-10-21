/**
 * DOM Utility Functions
 * 
 * @package FP\PerfSuite
 */

/**
 * Get WordPress messages from global config
 * 
 * @returns {object} Messages object
 */
export function getMessages() {
    const { fpPerfSuite = {} } = window;
    return fpPerfSuite.messages || {};
}

/**
 * Get WordPress REST URL from global config
 * 
 * @returns {string} REST URL
 */
export function getRestUrl() {
    const { fpPerfSuite = {} } = window;
    return fpPerfSuite.restUrl || '';
}