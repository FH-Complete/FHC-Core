/**
 * Performs a deep merge of objects and returns new object. Does not modify
 * objects (immutable) and merges arrays via concatenation.
 *
 * @param {...object} objects - Objects to merge
 * @returns {object} New object with merged key/values
 */
function mergeDeep(...objects) {
	const isObject = obj => obj && typeof obj === 'object';
	
	return objects.reduce((prev, obj) => {
		Object.keys(obj).forEach(key => {
			const pVal = prev[key];
			const oVal = obj[key];
			
			if (Array.isArray(pVal) && Array.isArray(oVal)) {
				prev[key] = pVal.concat(...oVal);
			}
			else if (isObject(pVal) && isObject(oVal)) {
				prev[key] = this.mergeDeep(pVal, oVal);
			}
			else {
				prev[key] = oVal;
			}
		});
		
		return prev;
	}, {});
}

/**
 * Extends VUEs toRaw() function to nested Proxies
 * @see https://www.reddit.com/r/javascript/comments/10gzynk/deep_cloning_objects_in_javascript_the_modern_way/
 *
 * @param object		sourceObj - Object to transform
 * @returns object
 */
function deepToRaw(sourceObj) {
	const objectIterator = input => {
		if (Array.isArray(input))
			return input.map(objectIterator);
		if (Vue.isRef(input) || Vue.isReactive(input) || Vue.isProxy(input))
			return objectIterator(Vue.toRaw(input));
		if (input && typeof input === 'object') {
			/** use custom handling of 'Date' objects to avoid data loss if treating it like any other object.
			 * reminder:
			 * typeof (new Date())        ==> 'object'
			 * Object.keys(new Date())    ==> []
			 */
			if (input instanceof Date)
				return input;

			return Object.keys(input).reduce((acc, key) => {
				acc[key] = objectIterator(input[key]);
				return acc;
			}, {});
		}

		return input;
	};
	
	return objectIterator(sourceObj);
}

export {
	mergeDeep,
	deepToRaw
}
export default {
	mergeDeep,
	deepToRaw
}