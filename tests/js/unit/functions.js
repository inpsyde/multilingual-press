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
export const getRandomArray = ( min = 0, max = 10, value ) => {
	const a = [];

	for ( let i = 0; i < getRandomInteger( min, max ); i++ ) {
		a.push( value || getRandomString() );
	}

	return a;
};

export const getRandomObject = ( min = 0, max = 10, value ) => {
	const o = {};

	for ( let i = 0; i < getRandomInteger( min, max ); i++ ) {
		o[ `element-${i}` ] = value || getRandomString();
	}

	return o;
};
