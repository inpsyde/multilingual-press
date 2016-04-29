//
// Independent functions.
//
export const getRandomBool = () => Math.random() < .5;

export const getRandomInteger = ( min = 0, max = 10e14 - 1 ) => Math.floor( Math.random() * ( max - min + 1 ) ) + min;

export const getRandomString = ( length = 16 ) => Math.random().toString( 36 ).slice( 2 ).substr( 0, length );

export const returnArg = ( arg ) => arg;

export const returnEmptyArray = () => [];

export const returnEmptyObject = () => { return {}; };

export const returnEmptyString = () => '';

export const returnFalse = () => false;

export const returnNull = () => null;

export const returnTrue = () => true;

export const returnUndefined = () => undefined;

//
// Dependent (!) functions.
//
export const getRandomArray = ( max = 10, value ) => {
	const a = [];

	for ( let i = 0; i < getRandomInteger( 0, max ); i++ ) {
		a.push( value || getRandomString() );
	}

	return a;
};

export const getRandomObject = ( max = 10, value ) => {
	const o = {};

	for ( let i = 0; i < getRandomInteger( 0, max ); i++ ) {
		o[ 'element' + i ] = value || getRandomString();
	}

	return o;
};
