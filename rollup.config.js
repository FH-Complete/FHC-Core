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

const fhcbasepath = import.meta.dirname;
let apps = {};
let curapp = null;

function FhcResolver () {
  return {
    name: 'fhc-resolver', // this name will show up in logs and errors
    buildStart (options) {
      //console.log('options: ' + JSON.stringify(options));
      //console.log('apps: ' + JSON.stringify(apps));
      curapp = apps[options.input[0]];
      //console.log('curapp: ' + curapp);
    },
    resolveId ( source, importer, options ) {
      //console.log('options: ' + JSON.stringify(options));
      //console.log('BH-FHC-BASEPATH: ' + fhcbasepath);
      if( source.includes('vueDatepicker.js.php') ) {
	return {id: 'vueDatepicker.js.php', external: 'relative'};
      }
      if( source.includes('index.ci.php') ) {
        //console.log('source: ' + source + ' curapp: ' + curapp + ' importer: ' + importer);
	let source_abs = fhcbasepath + '/' + source.replace(/(\.\.\/)+/, '');
	let source_rel = path.relative(path.dirname(curapp), source_abs);
	//console.log('SOURCE_ABS:' + source_abs + 'APP: ' + curapp + 'SOURCE_REL: ' + source_rel);
	return { id: source_rel, external: 'relative'};
      }
      if( source.includes('.php')) {
        console.log('source: ' + source + ' curapp: ' + curapp + ' importer: ' + importer);
	let source_abs = path.resolve(path.dirname(importer), source);
	if(source_abs.match(/\/FHC-Core-[^\/]+\/public\//)) {
	  source_abs = fhcbasepath + source_abs.replace(/^.+?\/(FHC-Core-[^\/]+)\/public\//, '/public/extensions/$1/');
	}
	let source_rel = path.relative(path.dirname(curapp), source_abs);
	console.log('SOURCE_ABS:' + source_abs + 'APP: ' + curapp + 'SOURCE_REL: ' + source_rel);
	return { id: source_rel, external: 'relative'};
      }
      //console.log('source: ' + source + ' options.isEntry: ' + options.isEntry + ' importer: ' + importer);
      if(importer === undefined) {
        return null;
      }
      if(!path.isAbsolute(source)) {
        let tmp = path.dirname(importer);
	if( importer.includes('/application/') ) {
	  tmp = tmp.replace(/public\/js\//, 'js/').replace(/\/application\//, '/public/');
	}
	else if( importer.includes('/FHC-Core-') ) {
      	  //console.log('RELATIV: source: ' + source + ' options.isEntry: ' + options.isEntry + ' importer: ' + importer);
	  tmp = fhcbasepath + tmp.replace(/^.+?\/(FHC-Core-[^\/]+)\/public\//, '/public/extensions/$1/');
	  //console.log('BHTMP: ' + tmp);
	}
      	const resolved = path.resolve(tmp, source);
	//console.log('BHRESOLVED: ' + resolved);
	if( existsSync(resolved) ) {
	  return resolved
	}
      } else {
      	//console.log('ABSOLUT: source: ' + source + ' options.isEntry: ' + options.isEntry + ' importer: ' + importer);
	return source;
      }
      return null; // other ids should be handled as usually
    }
  };
}

const useplugins = [
    	alias({
		entries: {
			vue: 'vue/dist/vue.esm-bundler.js',
		}
	}),
	commonjs(),
	nodeResolve({
		module: true,
		jsnext: true,
		preferBuiltins: true,
		browser: true,
		moduleDirectories: ['node_modules'],
		modulePaths: globSync('application/extensions/*/node_modules', {follow: true, realpath: true}).map(file => 
			fileURLToPath(new URL(file, import.meta.url))
		),
	}),
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
/*
	postcss({
		extract: false,
		modules: true,
		use: ['sass'],
	}),
*/
	terser()
];

export default globSync('public/**/js/apps/**/*.js', {follow: false, realpath: false}).map(file => { 
			if( path.dirname(file).includes('/dist/') || path.dirname(file).includes('/apps/vbform') ) {
				return null;
			}

			//console.log('RAWFILE: ' + file);

			const rel_app_path = (file.replace(/\/public/, '')).replace(/^\.\.\//, 'public/extensions/');
			const tmp = fileURLToPath(new URL(rel_app_path, import.meta.url));
			//console.log('BHTEST: ' + tmp);
			const basepath = tmp.replace(/\/public\/js\/.*/, '/public/js/');
			const outputfile = tmp.replace(/public\//, 'public/dist/');
			apps[tmp] = outputfile;
			//console.log('OUTFILE: ' + outputfile);
			let cssfile = path.basename(tmp.replace(/\.js/, '.css'));
			//console.log('cssfile: ' + cssfile);
			let cssplugin = [
			  postcss({
				extract: cssfile,
				minimize: true,
				sourceMap: true
			  })
			];
			const vuedatepicker = path.relative(path.dirname(outputfile), basepath + 'components/vueDatepicker.js.php');
			//console.log(vuedatepicker);
			return {
				input: tmp,
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
					paths: {
						"vueDatepicker.js.php": vuedatepicker
					}
				}
			}
		}).filter(Boolean);
