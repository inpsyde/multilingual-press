//
// Independent functions.
//
export const getRandomBool = () => .5 > Math.random();

export const getRandomInteger = ( min = 0, max = 10e14 - 1 ) => Math.floor( Math.random() * ( max - min + 1 ) ) + min;

export const getRandomString = ( length = 16 ) => Math.random().toString( 36 ).slice( 2 ).substr( 0, length );

export const returnArg = ( arg ) => arg;

export const returnEmptyArray = () => [];

export const returnEmptyObject = () => ( {} );

export const returnEmptyString = () => '';

export const returnFalse = () => false;

export const returnNull = () => null;

export const returnTrue = () => true;

export const returnUndefined = () => undefined;

//
// Dependent (!) functions.
//
export const getRandomArray = ( min = 0, max = 10, valueOrCallback = getRandomString ) => {
	const a = [];

	const valueCallback = 'function' === typeof valueOrCallback
		? valueOrCallback
		: () => valueOrCallback;

	for ( let i = 0; i < getRandomInteger( min, max ); i++ ) {
		a.push( valueCallback() );
	}

	return a;
};

export const getRandomBoolArray = ( min = 0, max = 10 ) => getRandomArray( min, max, getRandomBool );

export const getRandomIntegerArray = ( min = 0, max = 10 ) => getRandomArray( min, max, getRandomInteger );

export const getRandomObject = ( min = 0, max = 10, valueOrCallback = getRandomString ) => {
	const o = {};

	const valueCallback = 'function' === typeof valueOrCallback
		? valueOrCallback
		: () => valueOrCallback;

	for ( let i = 0; i < getRandomInteger( min, max ); i++ ) {
		o[ `element-${i}` ] = valueCallback();
	}

	return o;
};

export const getRandomBoolObject = ( min = 0, max = 10 ) => getRandomObject( min, max, getRandomBool );

export const getRandomIntegerObject = ( min = 0, max = 10 ) => getRandomObject( min, max, getRandomInteger );
