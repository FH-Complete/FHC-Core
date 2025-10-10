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

function FhcResolver () {
  return {
    name: 'fhc-resolver', // this name will show up in logs and errors
    resolveId ( source, importer, options ) {
      if( source.includes('vueDatepicker.js.php') ) {
	return {id: 'vueDatepicker.js.php', external: 'relative'};
      }
      if( source.includes('index.ci.php') ) {
	return { id: '../' + source, external: 'relative'};
      }
      if( source.includes('.php') ) {
	return false;
      }
      //console.log('source: ' + source + ' options.isEntry: ' + options.isEntry + ' importer: ' + importer);
      if(importer !== undefined && !options.isEntry && !path.isAbsolute(source)) {
        let tmp = path.dirname(importer);
	if( importer.includes('/application/') ) {
	  tmp = tmp.replace(/public\/js\//, 'js/').replace(/\/application\//, '/public/');
	}
      	const resolved = path.resolve(tmp, source);
	//console.log(resolved);
	if( existsSync(resolved) ) {
	  return resolved
	}
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

export default globSync('public/**/js/apps/**/*.js', {follow: true, realpath: true}).map(file => { 
			if( path.dirname(file).includes('/dist/') || path.dirname(file).includes('/apps/vbform') ) {
				return null;
			}
			const tmp = fileURLToPath(new URL(file, import.meta.url));
			const basepath = tmp.replace(/\/public\/js\/.*/, '/public/js/');
			const outputfile = tmp.replace(/public\//, 'public/dist/');
			let cssfile = path.basename(tmp.replace(/\.js/, '.css'));
			console.log('cssfile: ' + cssfile);
			let cssplugin = [
			  postcss({
				extract: cssfile,
				minimize: true,
				sourceMap: true
			  })
			];
			const vuedatepicker = path.relative(path.dirname(outputfile), basepath + 'components/vueDatepicker.js.php');
			console.log(vuedatepicker);
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
