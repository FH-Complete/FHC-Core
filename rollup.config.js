import babel from '@rollup/plugin-babel';
import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import vue from "rollup-plugin-vue";
import { globSync } from 'glob';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import postcss from 'rollup-plugin-postcss';
import replace from '@rollup/plugin-replace';
import alias from '@rollup/plugin-alias';
import { existsSync } from 'node:fs';
import terser from '@rollup/plugin-terser';
import json from '@rollup/plugin-json';

const debug = false;

const buildtimestamp = new Date().toISOString().replace(/[-T:]/g, '').substr(0,14);
const fhcbasepath = import.meta.dirname;

let apps = {};
let curapp = null;

function FhcResolver () {
  return {
    name: 'fhc-resolver', // this name will show up in logs and errors
    buildStart (options) {
      curapp = apps[options.input[0]];

      if(debug)
      {
	console.log('--------------------------------');
	console.log('fhc-resolver buildStart');
        console.log('options: ' + JSON.stringify(options, null, "\t"));
        console.log('apps: ' + JSON.stringify(apps, null, "\t"));
        console.log('curapp: ' + curapp);
	console.log('--------------------------------');
      }
    },
    resolveId ( source, importer, options ) {
      debug && console.log('source: ' + source + ' curapp: ' + curapp + ' importer: ' + importer);

      if(importer === undefined) {
        return null;
      }

      if( source.includes('index.ci.php') ) {
	let source_abs = fhcbasepath + '/' + source.replace(/(\.\.\/)+/, '');
	let source_rel = path.relative(path.dirname(curapp), source_abs) + '?' + buildtimestamp;

	debug && console.log('SOURCE_ABS:' + source_abs + 'APP: ' + curapp + 'SOURCE_REL: ' + source_rel);

	return { id: source_rel, external: 'relative'};
      }

      if( source.includes('.php') ) {
	let source_abs = path.resolve(path.dirname(importer), source);
	if(source_abs.match(/\/FHC-Core-[^\/]+\/public\//)) {
	  source_abs = fhcbasepath + source_abs.replace(/^.+?\/(FHC-Core-[^\/]+)\/public\//, '/public/extensions/$1/');
	}
	let source_rel = path.relative(path.dirname(curapp), source_abs) + '?' + buildtimestamp;

	debug && console.log('SOURCE_ABS:' + source_abs + 'APP: ' + curapp + 'SOURCE_REL: ' + source_rel);

	return { id: source_rel, external: 'relative'};
      }

      let resolved = null;

      if(!path.isAbsolute(source)) {
        let tmp = path.dirname(importer);

	if( importer.includes('/application/') ) {
	  tmp = tmp.replace(/public\/js\//, 'js/').replace(/\/application\//, '/public/');
	}
	else if( importer.includes('/FHC-Core-') && !importer.includes('/extensions/') ) {
	  tmp = fhcbasepath + tmp.replace(/^.+?\/(FHC-Core-[^\/]+)\/public\//, '/public/extensions/$1/');
	}

	resolved = path.resolve(tmp, source);
      } else {
	resolved = source;

	if( source.includes('/FHC-Core-') && !source.includes('/extensions/') ) 
	{
	  resolved = fhcbasepath + source.replace(/^.+?\/(FHC-Core-[^\/]+)\/public\//, '/public/extensions/$1/');
	}
      }

      if( resolved !== null && !existsSync(resolved) ) {
        console.log('not existsSync: ' + resolved + ' source: ' + source + ' importer: ' + importer);
      }

      return resolved;
    }
  };
}

const useplugins = [
	json({
		compact: true
	}),
	FhcResolver(),
	replace({
		preventAssignment: true,
		'process.env.NODE_ENV': JSON.stringify( 'production' ),
	}),
	vue(),
	babel({
		babelHelpers: 'bundled',
		plugins: ['transform-class-properties'],
	}),
//	terser()
];

export default globSync('public/**/js/apps/**/*.js', {follow: false, realpath: false}).map(file => { 
			if( path.dirname(file).includes('/dist/') 
			 || path.dirname(file).includes('/apps/vbform') 
			 || path.dirname(file).includes('/apps/api')
			 || path.basename(file) === 'common.js'
			) {
				return null;
			}

			const inputfile = fhcbasepath + '/' + file;
			const outputfile = inputfile.replace(/public\//, 'public/dist/');
			const cssfile = path.basename(inputfile.replace(/\.js/, '.css'));

			apps[inputfile] = outputfile;

			if(debug)
			{
			  console.log('--------------------------------');
			  console.log('fhcbasepath: ' + fhcbasepath);
			  console.log('file: ' + file);
			  console.log('inputfile: ' + inputfile);
			  console.log('outputfile: ' + outputfile);
			  console.log('cssfile: ' + cssfile);
			  console.log('--------------------------------');
			}

			const cssplugin = [
			  postcss({
				extract: cssfile,
				minimize: true,
				sourceMap: true
			  })
			];

			return {
				input: inputfile,
				plugins: [...useplugins, ...cssplugin],
				watch: {
					buildDelay: 500
				},
				output: {
					preserveModules: false,
					inlineDynamicImports: true,
					sourcemap: true,
					format: 'es',
					file: outputfile,
				}
			}
		}).filter(Boolean);
