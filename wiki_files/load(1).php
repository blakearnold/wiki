function isCompatible(ua) {
    return ! !((function() {
        'use strict';
        return !this&&Function.prototype.bind&&window.JSON;
    }

    ())&&'querySelector'in document&&'localStorage'in window&&'addEventListener'in window&& !ua.match(/MSIE 10|webOS\/1\.[0-4]|SymbianOS|Series60|NetFront|Opera Mini|S40OviBrowser|MeeGo|Android.+Glass|^Mozilla\/5\.0 .+ Gecko\/$|googleweblight|PLAYSTATION|PlayStation/));
}

if( !isCompatible(navigator.userAgent)) {
    document.documentElement.className=document.documentElement.className.replace(/(^|\s)client-js(\s|$)/, '$1client-nojs$2');

    while(window.NORLQ&&window.NORLQ[0]) {
        window.NORLQ.shift()();
    }

    window.NORLQ= {
        push:function(fn) {
            fn();
        }
    }

    ;

    window.RLQ= {
        push:function() {}
    }

    ;
}

else {
    if(window.performance&&performance.mark) {
        performance.mark('mwStartup');
    }

    (function() {
        'use strict';
        var mw, StringSet, log, hasOwn=Object.prototype.hasOwnProperty;

        function fnv132(str) {
            var hash=0x811C9DC5, i=0;
            for(;
            i<str.length;

            i++) {
                hash+=(hash<<1)+(hash<<4)+(hash<<7)+(hash<<8)+(hash<<24);
                hash^=str.charCodeAt(i);
            }

            hash=(hash>>>0). toString(36);

            while(hash.length<7) {
                hash='0'+hash;
            }

            return hash;
        }

        function defineFallbacks() {
            StringSet=window.Set||function() {
                var set=Object.create(null);

                return {
                    add:function(value) {
                        set[value]= !0;
                    }

                    , has:function(value) {
                        return value in set;
                    }
                }

                ;
            }

            ;
        }

        function setGlobalMapValue(map, key, value) {
            map.values[key]=value;
            log.deprecate(window, key, value, map===mw.config&&'Use mw.config instead.');
        }

        function logError(topic, data) {
            var msg, e=data.exception, console=window.console;

            if(console&&console.log) {
                msg=(e?'Exception': 'Error')+' in '+data.source+(data.module?' in module '+data.module:'')+(e?':':'.');
                console.log(msg);

                if(e&&console.warn) {
                    console.warn(e);
                }
            }
        }

        function Map(global) {
            this.values=Object.create(null);

            if(global===true) {
                this.set=function(selection, value) {
                    var s;

                    if(arguments.length>1) {
                        if(typeof selection==='string') {
                            setGlobalMapValue(this, selection, value);
                            return true;
                        }
                    }

                    else if(typeof selection==='object') {
                        for(s in selection) {
                            setGlobalMapValue(this, s, selection[s]);
                        }

                        return true;
                    }

                    return false;
                }

                ;
            }
        }

        Map. prototype= {
            constructor:Map, get:function(selection, fallback) {
                var results, i;
                fallback=arguments.length>1?fallback: null;

                if(Array.isArray(selection)) {
                    results= {}

                    ;
                    for(i=0;
                    i<selection.length;

                    i++) {
                        if(typeof selection[i]==='string') {
                            results[selection[i]]=selection[i]in this.values?this.values[selection[i]]: fallback;
                        }
                    }

                    return results;
                }

                if(typeof selection==='string') {
                    return selection in this.values?this.values[selection]: fallback;
                }

                if(selection===undefined) {
                    results= {}

                    ;

                    for(i in this.values) {
                        results[i]=this.values[i];
                    }

                    return results;
                }

                return fallback;
            }

            , set:function(selection, value) {
                var s;

                if(arguments.length>1) {
                    if(typeof selection==='string') {
                        this.values[selection]=value;
                        return true;
                    }
                }

                else if(typeof selection==='object') {
                    for(s in selection) {
                        this.values[s]=selection[s];
                    }

                    return true;
                }

                return false;
            }

            , exists:function(selection) {
                var i;

                if(Array.isArray(selection)) {
                    for(i=0;
                    i<selection.length;

                    i++) {
                        if(typeof selection[i] !=='string'|| !(selection[i]in this.values)) {
                            return false;
                        }
                    }

                    return true;
                }

                return typeof selection==='string'&&selection in this.values;
            }
        }

        ;
        defineFallbacks();

        log=(function() {
            var log=function() {}

            , console=window.console;

            log.warn=console&&console.warn?Function.prototype.bind.call(console.warn, console):function() {}

            ;

            log.error=console&&console.error?Function.prototype.bind.call(console.error, console):function() {}

            ;

            log.deprecate=function(obj, key, val, msg, logName) {
                var stacks;

                function maybeLog() {
                    var name=logName||key, trace=new Error().stack;

                    if( !stacks) {
                        stacks=new StringSet();
                    }

                    if( !stacks.has(trace)) {
                        stacks.add(trace);

                        if(logName||obj===window) {
                            mw.track('mw.deprecate', name);
                        }

                        mw.log.warn('Use of "'+name+'" is deprecated.'+(msg?' '+msg:''));
                    }
                }

                try {
                    Object.defineProperty(obj, key, {
                        configurable: !0, enumerable: !0, get:function() {
                            maybeLog();
                            return val;
                        }

                        , set:function(newVal) {
                            maybeLog();
                            val=newVal;
                        }
                    }

                    );
                }

                catch(err) {
                    obj[key]=val;
                }
            }

            ;
            return log;
        }

        ());

        mw= {
            redefineFallbacksForTest:function() {
                if( !window.QUnit) {
                    throw new Error('Not allowed');
                }

                defineFallbacks();
            }

            , now:function() {
                var perf=window.performance, navStart=perf&&perf.timing&&perf.timing.navigationStart;

                mw.now=navStart&&perf.now?function() {
                    return navStart+perf.now();
                }

                :Date.now;
                return mw.now();
            }

            , trackQueue:[], track:function(topic, data) {
                mw.trackQueue.push( {
                    topic: topic, timeStamp:mw.now(), data:data
                }

                );
            }

            , trackError:function(topic, data) {
                mw.track(topic, data);
                logError(topic, data);
            }

            , Map:Map, config:null, libs: {}

            , legacy: {}

            , messages:new Map(), templates:new Map(), log:log, loader:(function() {
                var registry=Object.create(null), sources=Object.create(null), handlingPendingRequests= !1, pendingRequests=[], queue=[], jobs=[], willPropagate= !1, errorModules=[], baseModules=["jquery", "mediawiki.base", "mediawiki.legacy.wikibits"], marker=document.querySelector('meta[name="ResourceLoaderDynamicStyles"]'), nextCssBuffer, rAF=window.requestAnimationFrame||setTimeout;

                function newStyleTag(text, nextNode) {
                    var el=document.createElement('style');
                    el.appendChild(document.createTextNode(text));

                    if(nextNode&&nextNode.parentNode) {
                        nextNode.parentNode.insertBefore(el, nextNode);
                    }

                    else {
                        document.head.appendChild(el);
                    }

                    return el;
                }

                function flushCssBuffer(cssBuffer) {
                    var i;
                    cssBuffer.active= !1;
                    newStyleTag(cssBuffer.cssText, marker);
                    for(i=0;
                    i<cssBuffer.callbacks.length;

                    i++) {
                        cssBuffer.callbacks[i]();
                    }
                }

                function addEmbeddedCSS(cssText, callback) {
                    if( !nextCssBuffer||nextCssBuffer.active===false||cssText.slice(0, '@import'.length)==='@import') {
                        nextCssBuffer= {
                            cssText: '', callbacks:[], active:null
                        }

                        ;
                    }

                    nextCssBuffer.cssText+='\n'+cssText;
                    nextCssBuffer.callbacks.push(callback);

                    if(nextCssBuffer.active===null) {
                        nextCssBuffer.active= !0;
                        rAF(flushCssBuffer.bind(null, nextCssBuffer));
                    }
                }

                function getCombinedVersion(modules) {
                    var hashes=modules.reduce(function(result, module) {
                        return result+registry[module].version;
                    }

                    , '');
                    return fnv132(hashes);
                }

                function allReady(modules) {
                    var i=0;
                    for(;
                    i<modules.length;

                    i++) {
                        if(mw.loader.getState(modules[i]) !=='ready') {
                            return false;
                        }
                    }

                    return true;
                }

                function allWithImplicitReady(module) {
                    return allReady(registry[module].dependencies)&&(baseModules.indexOf(module) !==-1|| allReady(baseModules));
                }

                function anyFailed(modules) {
                    var state, i=0;
                    for(;
                    i<modules.length;

                    i++) {
                        state=mw.loader.getState(modules[i]);

                        if(state==='error'||state==='missing') {
                            return true;
                        }
                    }

                    return false;
                }

                function doPropagation() {
                    var errorModule, baseModuleError, module, i, failed, job, didPropagate= !0;

                    do {
                        didPropagate= !1;

                        while(errorModules.length) {
                            errorModule=errorModules.shift();
                            baseModuleError=baseModules.indexOf(errorModule) !==-1;

                            for(module in registry) {
                                if(registry[module].state !=='error'&&registry[module].state !=='missing') {
                                    if(baseModuleError&&baseModules.indexOf(module)===-1) {
                                        registry[module].state='error';
                                        didPropagate= !0;
                                    }

                                    else if(registry[module].dependencies.indexOf(errorModule) !==-1) {
                                        registry[module].state='error';
                                        errorModules.push(module);
                                        didPropagate= !0;
                                    }
                                }
                            }
                        }

                        for(module in registry) {
                            if(registry[module].state==='loaded'&&allWithImplicitReady(module)) {
                                execute(module);
                                didPropagate= !0;
                            }
                        }

                        for(i=0;
                        i<jobs.length;

                        i++) {
                            job=jobs[i];
                            failed=anyFailed(job.dependencies);

                            if(failed||allReady(job. dependencies)) {
                                jobs.splice(i, 1);
                                i-=1;

                                try {
                                    if(failed&&job.error) {
                                        job.error(new Error('Failed dependencies'), job.dependencies);
                                    }

                                    else if( !failed&&job.ready) {
                                        job.ready();
                                    }
                                }

                                catch(e) {
                                    mw.trackError('resourceloader.exception', {
                                        exception: e, source:'load-callback'
                                    }

                                    );
                                }

                                didPropagate= !0;
                            }
                        }
                    }

                    while(didPropagate);
                    willPropagate= !1;
                }

                function requestPropagation() {
                    if(willPropagate) {
                        return;
                    }

                    willPropagate= !0;

                    mw.requestIdleCallback(doPropagation, {
                        timeout: 1
                    }

                    );
                }

                function setAndPropagate(module, state) {
                    registry[module].state=state;

                    if(state==='loaded'||state==='ready'||state==='error'||state==='missing') {
                        if(state==='ready') {
                            mw.loader.store.add(module);
                        }

                        else if(state==='error'||state==='missing') {
                            errorModules.push(module);
                        }

                        requestPropagation();
                    }
                }

                function sortDependencies(module, resolved, unresolved) {
                    var i, skip, deps;

                    if( !(module in registry)) {
                        throw new Error('Unknown module: '+module);
                    }

                    if(typeof registry[module].skip==='string') {
                        skip=(new Function(registry[module].skip)());
                        registry[module].skip= ! !skip;

                        if(skip) {
                            registry[module].dependencies=[];
                            setAndPropagate(module, 'ready');
                            return;
                        }
                    }

                    if( !unresolved) {
                        unresolved=new StringSet();
                    }

                    deps=registry[module].dependencies;
                    unresolved.add(module);
                    for(i=0;
                    i<deps.length;

                    i++) {
                        if(resolved.indexOf(deps[i])===-1) {
                            if(unresolved.has(deps[i])) {
                                throw new Error('Circular reference detected: '+module+' -> '+deps[i]);
                            }

                            sortDependencies(deps[i], resolved, unresolved);
                        }
                    }

                    resolved.push(module);
                }

                function resolve(modules) {
                    var resolved=baseModules.slice(), i=0;
                    for(;
                    i<modules.length;

                    i++) {
                        sortDependencies(modules[i], resolved);
                    }

                    return resolved;
                }

                function resolveStubbornly(modules) {
                    var saved, resolved=baseModules.slice(), i=0;
                    for(;
                    i<modules.length;

                    i++) {
                        saved=resolved.slice();

                        try {
                            sortDependencies(modules[i], resolved);
                        }

                        catch(err) {
                            resolved=saved;

                            mw.trackError('resourceloader.exception', {
                                exception: err, source:'resolve'
                            }

                            );
                        }
                    }

                    return resolved;
                }

                function resolveRelativePath(relativePath, basePath) {
                    var prefixes, prefix, baseDirParts, relParts=relativePath.match(/^((?: \.\.?\/)+)(.*)$/);

                    if( !relParts) {
                        return null;
                    }

                    baseDirParts=basePath.split('/');
                    baseDirParts.pop();
                    prefixes=relParts[1].split('/');
                    prefixes.pop();

                    while((prefix=prefixes.pop()) !==undefined) {
                        if(prefix==='..') {
                            baseDirParts.pop();
                        }
                    }

                    return(baseDirParts.length?baseDirParts.join('/')+'/':'')+relParts[2];
                }

                function makeRequireFunction(moduleObj, basePath) {
                    return function require(moduleName) {
                        var fileName, fileContent, result, moduleParam, scriptFiles=moduleObj.script.files;
                        fileName=resolveRelativePath(moduleName, basePath);

                        if(fileName===null) {
                            return mw.loader.require(moduleName);
                        }

                        if( !hasOwn.call(scriptFiles, fileName)) {
                            throw new Error('Cannot require() undefined file '+fileName);
                        }

                        if(hasOwn.call(moduleObj.packageExports, fileName)) {
                            return moduleObj.packageExports[fileName];
                        }

                        fileContent=scriptFiles[fileName];

                        if(typeof fileContent==='function') {
                            moduleParam= {
                                exports: {}
                            }

                            ;
                            fileContent(makeRequireFunction(moduleObj, fileName), moduleParam);
                            result=moduleParam.exports;
                        }

                        else {
                            result=fileContent;
                        }

                        moduleObj.packageExports[fileName]=result;
                        return result;
                    }

                    ;
                }

                function addScript(src, callback) {
                    var script=document.createElement('script');
                    script.src=src;

                    script.onload=script.onerror=function() {
                        if(script.parentNode) {
                            script.parentNode.removeChild(script);
                        }

                        if(callback) {
                            callback();
                            callback=null;
                        }
                    }

                    ;
                    document.head.appendChild(script);
                }

                function queueModuleScript(src, moduleName, callback) {
                    pendingRequests.push(function() {
                        if(moduleName !=='jquery') {
                            window.require=mw.loader.require;
                            window.module=registry[moduleName].module;
                        }

                        addScript(src, function() {
                            delete window.module;
                            callback();

                            if(pendingRequests[0]) {
                                pendingRequests.shift()();
                            }

                            else {
                                handlingPendingRequests= !1;
                            }
                        }

                        );
                    }

                    );

                    if( !handlingPendingRequests&&pendingRequests[0]) {
                        handlingPendingRequests= !0;
                        pendingRequests.shift()();
                    }
                }

                function addLink(url, media, nextNode) {
                    var el=document.createElement('link');
                    el.rel='stylesheet';

                    if(media&&media !=='all') {
                        el.media=media;
                    }

                    el.href=url;

                    if(nextNode&&nextNode.parentNode) {
                        nextNode.parentNode.insertBefore(el, nextNode);
                    }

                    else {
                        document.head.appendChild(el);
                    }
                }

                function domEval(code) {
                    var script=document.createElement('script');

                    if(mw.config.get('wgCSPNonce') !==false) {
                        script.nonce=mw.config.get('wgCSPNonce');
                    }

                    script.text=code;
                    document.head.appendChild(script);
                    script.parentNode.removeChild(script);
                }

                function enqueue(dependencies, ready, error) {
                    if(allReady(dependencies)) {
                        if(ready !==undefined) {
                            ready();
                        }

                        return;
                    }

                    if(anyFailed(dependencies)) {
                        if(error !==undefined) {
                            error(new Error('One or more dependencies failed to load'), dependencies);
                        }

                        return;
                    }

                    if(ready !==undefined||error !==undefined) {
                        jobs.push( {
                            dependencies:dependencies.filter(function(module) {
                                var state=registry[module].state;
                                return state==='registered'||state==='loaded'||state==='loading'||state==='executing';
                            }

                            ), ready:ready, error:error
                        }

                        );
                    }

                    dependencies.forEach(function(module) {
                        if(registry[module].state==='registered'&&queue.indexOf(module)===-1) {
                            if(registry[module].group==='private') {
                                setAndPropagate(module, 'error');
                            }

                            else {
                                queue.push(module);
                            }
                        }
                    }

                    );
                    mw.loader.work();
                }

                function execute(module) {
                    var key, value, media, i, urls,
                    cssHandle, siteDeps, siteDepErr, runScript, cssPending=0;

                    if(registry[module].state !=='loaded') {
                        throw new Error('Module in state "'+registry[module].state+'" may not be executed: '+module);
                    }

                    registry[module].state='executing';

                    runScript=function() {
                        var script, markModuleReady, nestedAddScript, mainScript;
                        script=registry[module].script;

                        markModuleReady=function() {
                            setAndPropagate(module, 'ready');
                        }

                        ;

                        nestedAddScript=function(arr, callback, i) {
                            if(i>=arr.length) {
                                callback();
                                return;
                            }

                            queueModuleScript(arr[i], module, function() {
                                nestedAddScript(arr, callback, i+1);
                            }

                            );
                        }

                        ;

                        try {
                            if(Array.isArray(script)) {
                                nestedAddScript(script, markModuleReady, 0);
                            }

                            else if(typeof script==='function'||(typeof script==='object'&&script !==null)) {
                                if(typeof script==='function') {
                                    if(module==='jquery') {
                                        script();
                                    }

                                    else {
                                        script(window.$, window.$, mw.loader.require, registry[module].module);
                                    }
                                }

                                else {
                                    mainScript=script.files[script.main];

                                    if(typeof mainScript !=='function') {
                                        throw new Error('Main file '+script.main+' in module '+module+ ' must be of type function, found '+typeof mainScript);
                                    }

                                    mainScript(makeRequireFunction(registry[module], script.main), registry[module].module);
                                }

                                markModuleReady();
                            }

                            else if(typeof script==='string') {
                                domEval(script);
                                markModuleReady();
                            }

                            else {
                                markModuleReady();
                            }
                        }

                        catch(e) {
                            setAndPropagate(module, 'error');

                            mw.trackError('resourceloader.exception', {
                                exception: e, module:module, source:'module-execute'
                            }

                            );
                        }
                    }

                    ;

                    if(registry[module].messages) {
                        mw.messages.set(registry[module].messages);
                    }

                    if(registry[module].templates) {
                        mw.templates.set(module, registry[module].templates);
                    }

                    cssHandle=function() {
                        cssPending++;

                        return function() {
                            var runScriptCopy;
                            cssPending--;

                            if(cssPending===0) {
                                runScriptCopy=runScript;
                                runScript=undefined;
                                runScriptCopy();
                            }
                        }

                        ;
                    }

                    ;

                    if(registry[module].style) {
                        for(key in registry[module].style) {
                            value=registry[module].style[key];
                            media=undefined;

                            if(key !=='url'&&key !=='css') {
                                if(typeof value==='string') {
                                    addEmbeddedCSS(value, cssHandle());
                                }

                                else {
                                    media=key;
                                    key='bc-url';
                                }
                            }

                            if(Array.isArray(value)) {
                                for(i=0;
                                i<value.length;

                                i++) {
                                    if(key==='bc-url') {
                                        addLink(value[i], media, marker);
                                    }

                                    else if(key==='css') {
                                        addEmbeddedCSS(value[i], cssHandle());
                                    }
                                }
                            }

                            else if(typeof value==='object') {
                                for(media in value) {
                                    urls=value[media];
                                    for(i=0;
                                    i<urls.length;

                                    i++) {
                                        addLink(urls[i], media, marker);
                                    }
                                }
                            }
                        }
                    }

                    if(module==='user') {
                        try {
                            siteDeps=resolve(['site']);
                        }

                        catch(e) {
                            siteDepErr=e;
                            runScript();
                        }

                        if(siteDepErr===undefined) {
                            enqueue(siteDeps, runScript, runScript);
                        }
                    }

                    else if(cssPending===0) {
                        runScript();
                    }
                }

                function sortQuery(o) {
                    var key, sorted= {}

                    , a=[];

                    for(key in o) {
                        a.push(key);
                    }

                    a.sort();
                    for(key=0;
                    key<a.length;

                    key++) {
                        sorted[a[key]]=o[a[key]];
                    }

                    return sorted;
                }

                function buildModulesString(moduleMap) {
                    var p, prefix, str=[], list=[];

                    function restore(suffix) {
                        return p+suffix;
                    }

                    for(prefix in moduleMap) {
                        p=prefix===''?'': prefix+'.';
                        str.push(p+moduleMap[prefix].join(','));
                        list.push.apply(list, moduleMap[prefix].map(restore));
                    }

                    return {
                        str: str.join('|'), list:list
                    }

                    ;
                }

                function resolveIndexedDependencies(modules) {
                    var i, j, deps;

                    function resolveIndex(dep) {
                        return typeof dep==='number'?modules[dep][0]: dep;
                    }

                    for(i=0;
                    i<modules.length;

                    i++) {
                        deps=modules[i][2];

                        if(deps) {
                            for(j=0;
                            j<deps.length;

                            j++) {
                                deps[j]=resolveIndex(deps[j]);
                            }
                        }
                    }
                }

                function makeQueryString(params) {
                    return Object.keys(params).map(function(key) {
                        return encodeURIComponent(key)+'='+encodeURIComponent(params[key]);
                    }

                    ).join('&');
                }

                function batchRequest(batch) {
                    var reqBase, splits, b, bSource, bGroup, source, group, i, modules, sourceLoadScript, currReqBase, currReqBaseLength, moduleMap, currReqModules, l, lastDotIndex, prefix, suffix, bytesAdded;

                    function doRequest() {
                        var query=Object.create(currReqBase), packed=buildModulesString(moduleMap);
                        query.modules=packed.str;
                        query.version=getCombinedVersion(packed.list);
                        query=sortQuery(query);
                        addScript(sourceLoadScript+'?'+makeQueryString(query));
                    }

                    if( !batch.length) {
                        return;
                    }

                    batch.sort();

                    reqBase= {
                        skin: mw.config.get('skin'), lang:mw.config.get('wgUserLanguage'), debug:mw.config.get('debug')
                    }

                    ;
                    splits=Object.create(null);
                    for(b=0;
                    b<batch.length;

                    b++) {
                        bSource=registry[batch[b]].source;
                        bGroup=registry[ batch[b]].group;

                        if( !splits[bSource]) {
                            splits[bSource]=Object.create(null);
                        }

                        if( !splits[bSource][bGroup]) {
                            splits[bSource][bGroup]=[];
                        }

                        splits[bSource][bGroup].push(batch[b]);
                    }

                    for(source in splits) {
                        sourceLoadScript=sources[source];

                        for(group in splits[source]) {
                            modules=splits[source][group];
                            currReqBase=Object.create(reqBase);

                            if(group==='user'&&mw.config.get('wgUserName') !==null) {
                                currReqBase.user=mw.config.get('wgUserName');
                            }

                            currReqBaseLength=makeQueryString(currReqBase).length+25;
                            l=currReqBaseLength;
                            moduleMap=Object.create(null);
                            currReqModules=[];
                            for(i=0;
                            i<modules.length;

                            i++) {
                                lastDotIndex=modules[i].lastIndexOf('.');
                                prefix=modules[i].substr(0, lastDotIndex);
                                suffix=modules[i].slice(lastDotIndex+1);
                                bytesAdded=moduleMap[prefix]?suffix.length+3: modules[i].length+3;

                                if(currReqModules.length&&l+bytesAdded>mw.loader.maxQueryLength) {
                                    doRequest();
                                    l=currReqBaseLength;
                                    moduleMap=Object.create(null);
                                    currReqModules=[];

                                    mw.track('resourceloader.splitRequest', {
                                        maxQueryLength: mw.loader.maxQueryLength
                                    }

                                    );
                                }

                                if( ! moduleMap[prefix]) {
                                    moduleMap[prefix]=[];
                                }

                                l+=bytesAdded;
                                moduleMap[prefix].push(suffix);
                                currReqModules.push(modules[i]);
                            }

                            if(currReqModules.length) {
                                doRequest();
                            }
                        }
                    }
                }

                function asyncEval(implementations, cb) {
                    if( !implementations.length) {
                        return;
                    }

                    mw.requestIdleCallback(function() {
                        try {
                            domEval(implementations.join(';'));
                        }

                        catch(err) {
                            cb(err);
                        }
                    }

                    );
                }

                function getModuleKey(module) {
                    return module in registry?(module+'@'+registry[module].version): null;
                }

                function splitModuleKey(key) {
                    var index=key.indexOf('@');

                    if(index===-1) {
                        return {
                            name: key, version:''
                        }

                        ;
                    }

                    return {
                        name: key.slice(0, index), version:key.slice(index+1)
                    }

                    ;
                }

                function registerOne(module, version, dependencies, group, source, skip) {
                    if(module in registry) {
                        throw new Error('module already registered: '+module);
                    }

                    registry[module]= {
                        module: {
                            exports: {}
                        }

                        , packageExports: {}

                        , version:String(version||''), dependencies:dependencies||[], group:typeof group==='string'?group:null, source:typeof source==='string'?source:'local', state:'registered', skip:typeof skip==='string'?skip:null
                    }

                    ;
                }

                return {
                    moduleRegistry:registry, maxQueryLength:5000, addStyleTag:newStyleTag, enqueue:enqueue, resolve:resolve, work:function() {
                        var implementations, sourceModules, batch=[], q=0;
                        for(;
                        q<queue.length;

                        q++) {
                            if(queue[q]in registry&&registry[queue[q]].state==='registered') {
                                if(batch.indexOf(queue[q])===-1) {
                                    batch.push(queue[q]);
                                    registry[queue[q]].state='loading';
                                }
                            }
                        }

                        queue=[];

                        if( !batch.length) {
                            return;
                        }

                        mw.loader.store.init();

                        if(mw.loader.store.enabled) {
                            implementations=[];
                            sourceModules=[];

                            batch=batch.filter(function(module) {
                                var implementation=mw.loader.store.get(module);

                                if(implementation) {
                                    implementations.push(implementation);
                                    sourceModules.push(module);
                                    return false;
                                }

                                return true;
                            }

                            );

                            asyncEval(implementations, function(err) {
                                var failed;
                                mw.loader.store.stats.failed++;
                                mw.loader.store.clear();

                                mw.trackError('resourceloader.exception', {
                                    exception: err, source:'store-eval'
                                }

                                );

                                failed=sourceModules.filter(function(module) {
                                    return registry[module].state==='loading';
                                }

                                );
                                batchRequest(failed);
                            }

                            );
                        }

                        batchRequest(batch);
                    }

                    , addSource:function(ids) {
                        var id;

                        for(id in ids) {
                            if(id in sources) {
                                throw new Error('source already registered: '+id);
                            }

                            sources[id]=ids[id];
                        }
                    }

                    , register:function(modules) {
                        var i;

                        if(typeof modules==='object') {
                            resolveIndexedDependencies(modules);
                            for(i=0;
                            i<modules.length;

                            i++) {
                                registerOne.apply(null, modules[i]);
                            }
                        }

                        else {
                            registerOne.apply(null, arguments);
                        }
                    }

                    , implement:function(module, script, style, messages, templates) {
                        var split=splitModuleKey(module), name=split.name, version=split.version;

                        if( !(name in registry)) {
                            mw.loader.register(name);
                        }

                        if(registry[name].script !==undefined) {
                            throw new Error('module already implemented: '+name);
                        }

                        if(version) {
                            registry[name].version=version;
                        }

                        registry[name].script=script||null;
                        registry[name].style=style||null;
                        registry[name].messages=messages||null;
                        registry[name].templates=templates||null;

                        if(registry[name].state !=='error'&&registry[name].state !=='missing') {
                            setAndPropagate(name, 'loaded');
                        }
                    }

                    , load:function(modules, type) {
                        if(typeof modules==='string'&&/^(https?: )?\/?\ //.test(modules)){if(

                            type==='text/css') {
                            addLink(modules);
                        }

                        else if(type==='text/javascript'||type===undefined) {
                            addScript(modules);
                        }

                        else {
                            throw new Error('type must be text/css or text/javascript, found '+type);
                        }
                    }

                    else {
                        modules=typeof modules==='string'?[modules]: modules;
                        enqueue(resolveStubbornly(modules), undefined, undefined);
                    }
                }

                , state:function(states) {
                    var module, state;

                    for(module in states) {
                        state=states[module];

                        if( !(module in registry)) {
                            mw.loader.register(module);
                        }

                        setAndPropagate(module, state);
                    }
                }

                , getVersion:function(module) {
                    return module in registry?registry[module].version: null;
                }

                , getState:function(module) {
                    return module in registry?registry[module].state: null;
                }

                , getModuleNames:function() {
                    return Object.keys(registry);
                }

                , require:function(moduleName) {
                    var state=mw.loader.getState(moduleName);

                    if(state !=='ready') {
                        throw new Error('Module "'+moduleName+'" is not loaded');
                    }

                    return registry[moduleName].module.exports;
                }

                , store: {
                    enabled:null, MODULE_SIZE_MAX:100*1000, items: {}

                    , queue:[], stats: {
                        hits: 0, misses:0, expired:0, failed:0
                    }

                    ,
                    toJSON:function() {
                        return {
                            items: mw.loader.store.items, vary:mw.loader.store.getVary()
                        }

                        ;
                    }

                    , getStoreKey:function() {
                        return'MediaWikiModuleStore:'+mw.config.get('wgDBname');
                    }

                    , getVary:function() {
                        return mw.config.get('skin')+':'+mw.config.get('wgResourceLoaderStorageVersion')+':'+mw.config.get('wgUserLanguage');
                    }

                    , init:function() {
                        var raw, data;

                        if(this.enabled !==null) {
                            return;
                        }

                        if(/Firefox/.test(navigator.userAgent)|| !mw.config.get('wgResourceLoaderStorageEnabled')) {
                            this.clear();
                            this.enabled= !1;
                            return;
                        }

                        if(mw.config.get('debug')) {
                            this.enabled= !1;
                            return;
                        }

                        try {
                            raw=localStorage.getItem(this.getStoreKey());
                            this.enabled= !0;
                            data=JSON.parse(raw);

                            if(data&&typeof data.items==='object'&&data.vary===this.getVary()) {
                                this.items=data.items;
                                return;
                            }
                        }

                        catch(e) {}

                        if(raw===undefined) {
                            this.enabled= !1;
                        }
                    }

                    , get:function(module) {
                        var key;

                        if( !this.enabled) {
                            return false;
                        }

                        key=getModuleKey(module);

                        if(key in this.items) {
                            this.stats.hits++;
                            return this.items[key];
                        }

                        this.stats.misses++;
                        return false;
                    }

                    , add:function(module) {
                        if( ! this.enabled) {
                            return;
                        }

                        this.queue.push(module);
                        this.requestUpdate();
                    }

                    , set:function(module) {
                        var key, args, src, encodedScript, descriptor=mw.loader.moduleRegistry[module];
                        key=getModuleKey(module);

                        if(key in this.items|| !descriptor||descriptor.state !=='ready'|| !descriptor.version||descriptor.group==='private'||descriptor.group==='user'||[descriptor.script, descriptor.style, descriptor.messages, descriptor.templates].indexOf(undefined) !==-1) {
                            return;
                        }

                        try {
                            if(typeof descriptor.script==='function') {
                                encodedScript=String(descriptor.script);
                            }

                            else if(typeof descriptor.script==='object'&&descriptor.script&& !Array.isArray(descriptor.script)) {
                                encodedScript='{'+'main:'+JSON.stringify(descriptor.script.main)+','+'files:{'+Object.keys(descriptor.script.files).map(function(key) {
                                    var value=descriptor.script.files[key];
                                    return JSON.stringify(key)+':'+(typeof value==='function'?value: JSON.stringify(value));
                                }

                                ).join(',')+'}}';
                            }

                            else {
                                encodedScript=JSON.stringify(descriptor.script);
                            }

                            args=[JSON.stringify(key), encodedScript, JSON.stringify(descriptor.style), JSON.stringify(descriptor.messages), JSON.stringify(descriptor.templates)];
                        }

                        catch(e) {
                            mw.trackError('resourceloader.exception', {
                                exception: e, source:'store-localstorage-json'
                            }

                            );
                            return;
                        }

                        src='mw.loader.implement('+args.join(',')+');';

                        if(src.length>this.MODULE_SIZE_MAX) {
                            return;
                        }

                        this.items[key]=src;
                    }

                    , prune:function() {
                        var key, module;

                        for(key in this.items) {
                            module=key.slice(0, key.indexOf('@'));

                            if(getModuleKey(module) !==key) {
                                this.stats.expired++;
                                delete this.items[key];
                            }

                            else if(this.items[key].length>this.MODULE_SIZE_MAX) {
                                delete this.items[key];
                            }
                        }
                    }

                    , clear:function() {
                        this.items= {}

                        ;

                        try {
                            localStorage.removeItem(this.getStoreKey());
                        }

                        catch(e) {}
                    }

                    , requestUpdate:(function() {
                        var hasPendingWrites= !1;

                        function flushWrites() {
                            var data, key;
                            mw.loader.store.prune();

                            while(mw.loader.store.queue.length) {
                                mw.loader.store.set(mw.loader.store.queue.shift());
                            }

                            key=mw.loader.store.getStoreKey();

                            try {
                                localStorage.removeItem(key);
                                data=JSON.stringify(mw.loader.store);
                                localStorage.setItem(key,
                                data);
                            }

                            catch(e) {
                                mw.trackError('resourceloader.exception', {
                                    exception: e, source:'store-localstorage-update'
                                }

                                );
                            }

                            hasPendingWrites= !1;
                        }

                        function onTimeout() {
                            mw.requestIdleCallback(flushWrites);
                        }

                        return function() {
                            if( !hasPendingWrites) {
                                hasPendingWrites= !0;
                                setTimeout(onTimeout, 2000);
                            }
                        }

                        ;
                    }

                    ())
                }
            }

            ;
        }

        ()), user: {
            options: new Map(), tokens:new Map()
        }

        , widgets: {}
    }

    ;
    window.mw=window.mediaWiki=mw;
}

());

(function() {
    var maxBusy=50;

    mw.requestIdleCallbackInternal=function(callback) {
        setTimeout(function() {
            var start=mw.now();

            callback( {
                didTimeout: !1, timeRemaining:function() {
                    return Math.max(0, maxBusy-(mw.now()-start));
                }
            }

            );
        }

        , 1);
    }

    ;
    mw.requestIdleCallback=window.requestIdleCallback?window.requestIdleCallback.bind(window):mw.requestIdleCallbackInternal;
}

());

(function() {
    mw.config=new mw.Map(true);

    mw.loader.addSource( {
        "local": "/w/load.php", "metawiki":"//meta.wikimedia.org/w/load.php"
    }

    );
    mw.loader.register([["site", "1cevkmp", [1]], ["site.styles", "0f6dvxu", [], "site"], ["noscript", "0r22l1o", [], "noscript"], ["filepage", "0spr0cd"
    ], ["user.groups", "07j6l8d", [5]], ["user", "0k1cuul", [], "user"], ["user.styles", "08fimpv", [], "user"], ["user.defaults", "1wulc8m"], ["user.options", "0r5ungb", [7], "private"], ["user.tokens", "0tffind", [], "private"], ["mediawiki.skinning.elements", "12ext4y"], ["mediawiki.skinning.content", "0uvluzg"], ["mediawiki.skinning.interface", "1fnaphl"], ["jquery.makeCollapsible.styles", "18b3tue"], ["mediawiki.skinning.content.parsoid", "1ipyzzq"], ["mediawiki.skinning.content.externallinks", "1gzzmgq"], ["jquery", "0gmhg1u"], ["mediawiki.base", "0cjjt0t", [16]], ["mediawiki.legacy.wikibits", "05hpy57", [16]], ["jquery.accessKeyLabel", "1hapo74", [24, 113]], ["jquery.async", "19x5bhh"], ["jquery.byteLength", "1mvezut", [114]], ["jquery.checkboxShiftClick", "0m21x1o"], ["jquery.chosen", "11bppto"], ["jquery.client", "1nc40rm"], ["jquery.color", "0815wm8", [26]], ["jquery.colorUtil", "0bi0x56"], ["jquery.confirmable", "06n1cuk", [158]], ["jquery.cookie", "12o00nd"], ["jquery.form", "0aamipo"], ["jquery.fullscreen", "00p9phm"], ["jquery.getAttrs",
    "0bcjlvq"], ["jquery.highlightText", "0ozekmh", [113]], ["jquery.hoverIntent", "0biveym"], ["jquery.i18n", "0yrugds", [157]], ["jquery.lengthLimit", "0sji1oy", [114]], ["jquery.makeCollapsible", "04y7lcr", [13]], ["jquery.mw-jump", "1szw96f"], ["jquery.qunit", "11kof1g"], ["jquery.spinner", "0bx0qb7"], ["jquery.jStorage", "0v6nblq"], ["jquery.suggestions", "0kf9epa", [32]], ["jquery.tabIndex", "02mw9ml"], ["jquery.tablesorter", "1x6t8mg", [44, 113, 159]], ["jquery.tablesorter.styles", "1ht55lp"], ["jquery.textSelection", "13js4wb", [24]], ["jquery.throttle-debounce", "06eecyr"], ["jquery.tipsy", "0yepdf6"], ["jquery.ui.core", "0qx9lar", [49], "jquery.ui"], ["jquery.ui.core.styles", "0fari4b", [], "jquery.ui"], ["jquery.ui.accordion", "1cc21wd", [48, 67], "jquery.ui"], ["jquery.ui.autocomplete", "0qcao9c", [57], "jquery.ui"], ["jquery.ui.button", "168uber", [48, 67], "jquery.ui"], ["jquery.ui.datepicker", "18be4vx", [48], "jquery.ui"], ["jquery.ui.dialog", "1j5ceqe", [52, 55, 59, 61], "jquery.ui"], ["jquery.ui.draggable", "0g83sq9", [48, 58], "jquery.ui"], [ "jquery.ui.droppable", "1wgxv2c", [55], "jquery.ui"], ["jquery.ui.menu", "1n2r2an", [48, 59, 67], "jquery.ui"], ["jquery.ui.mouse", "0j7j4vi", [67], "jquery.ui"], ["jquery.ui.position", "0c81it6", [], "jquery.ui"], ["jquery.ui.progressbar", "1s360q1", [48, 67], "jquery.ui"], ["jquery.ui.resizable", "1f75xdc", [48, 58], "jquery.ui"], ["jquery.ui.selectable", "1dd2njn", [48, 58], "jquery.ui"], ["jquery.ui.slider", "1y6rx93", [48, 58], "jquery.ui"], ["jquery.ui.sortable", "0l8yncv", [48, 58], "jquery.ui"], ["jquery.ui.tabs", "1xp8rtg", [48, 67], "jquery.ui"], ["jquery.ui.tooltip", "0scsytw", [48, 59, 67], "jquery.ui"], ["jquery.ui.widget", "0ve45kp", [], "jquery.ui"], ["jquery.effects.core", "1ag4q78", [], "jquery.ui"], ["jquery.effects.blind", "14vo2cd", [68], "jquery.ui"], ["jquery.effects.bounce", "0u0y31f", [68], "jquery.ui"], ["jquery.effects.clip", "1kvdyfi", [68], "jquery.ui"], ["jquery.effects.drop", "1xfrk7q", [68], "jquery.ui"], ["jquery.effects.explode", "1osv93j", [68], "jquery.ui"], ["jquery.effects.fade", "0ugaykz", [68], "jquery.ui"], ["jquery.effects.fold",
    "18b1djz", [68], "jquery.ui"], ["jquery.effects.highlight", "12rvk8n", [68], "jquery.ui"], ["jquery.effects.pulsate", "01lhhtr", [68], "jquery.ui"], ["jquery.effects.scale", "1a06vdb", [68], "jquery.ui"], ["jquery.effects.shake", "0mc7wls", [68], "jquery.ui"], ["jquery.effects.slide", "0up9xn7", [68], "jquery.ui"], ["jquery.effects.transfer", "0vy51uf", [68], "jquery.ui"], ["moment", "17cheks", [113, 155]], ["mediawiki.apihelp", "1qm36mn"], ["mediawiki.template", "0tqh6fm"], ["mediawiki.template.mustache", "0kue43n", [84]], ["mediawiki.template.regexp", "1ppu9k0", [84]], ["mediawiki.apipretty", "0vjfwbh"], ["mediawiki.api", "1lfnpdj", [118, 9]], ["mediawiki.content.json", "1ej96xn"], ["mediawiki.confirmCloseWindow", "0u2pg9b"], ["mediawiki.debug", "14uvojx", [252]], ["mediawiki.diff.styles", "1mxuj5d"], ["mediawiki.feedback", "1d2ioi6", [107, 257]], ["mediawiki.feedlink", "10k9o9x"], ["mediawiki.filewarning", "09cu3io", [252]], ["mediawiki.ForeignApi", "0451utn", [385]], ["mediawiki.ForeignApi.core", "1hj6uoc", [88, 248]], ["mediawiki.helplink", "1390usa"],
    ["mediawiki.hlist", "0i5k2dm"], ["mediawiki.htmlform", "0o4wd7s", [35, 113]], ["mediawiki.htmlform.checker", "03n31dt", [46]], ["mediawiki.htmlform.ooui", "0qx7he6", [252]], ["mediawiki.htmlform.styles", "00iuug1"], ["mediawiki.htmlform.ooui.styles", "1f29ffm"], ["mediawiki.icon", "0r30c5u"], ["mediawiki.inspect", "0cq1qr4", [113, 114]], ["mediawiki.messagePoster", "0l54pox", [96]], ["mediawiki.messagePoster.wikitext", "1xodl3v", [107]], ["mediawiki.notification", "1bkw1nt", [130, 137]], ["mediawiki.notify", "08ef6pm"], ["mediawiki.notification.convertmessagebox", "1udpxkk", [109]], ["mediawiki.notification.convertmessagebox.styles", "0nmyk2k"], ["mediawiki.RegExp", "0kzono7"], ["mediawiki.String", "0oxp0ra"], ["mediawiki.pager.tablePager", "0uckkbp"], ["mediawiki.searchSuggest", "19ptwll", [31, 41, 88, 8]], ["mediawiki.storage", "15uaf9w"], ["mediawiki.Title", "16sfpsg", [114, 130]], ["mediawiki.Upload", "18yzv3f", [88]], ["mediawiki.ForeignUpload", "0dt4yu8", [96, 119]], ["mediawiki.ForeignStructuredUpload", "13x3986", [120]], [ "mediawiki.Upload.Dialog", "027w2mq", [123]], ["mediawiki.Upload.BookletLayout", "1x870jh", [119, 158, 128, 244, 82, 254, 257]], ["mediawiki.ForeignStructuredUpload.BookletLayout", "0h2hb85", [121, 123, 162, 231, 225]], ["mediawiki.toc", "1up9gnx", [134]], ["mediawiki.toc.styles", "0vurclo"], ["mediawiki.Uri", "0dukcku", [130, 86]], ["mediawiki.user", "05zjnmc", [88, 117, 8]], ["mediawiki.userSuggest", "0eya1z7", [41, 88]], ["mediawiki.util", "0zoephy", [19]], ["mediawiki.viewport", "06gdr2b"], ["mediawiki.checkboxtoggle", "00w9tlo"], ["mediawiki.checkboxtoggle.styles", "1u6gth1"], ["mediawiki.cookie", "1h9hppd", [28]], ["mediawiki.experiments", "0rgmhag"], ["mediawiki.editfont.styles", "04e3p4i"], ["mediawiki.visibleTimeout", "0tu6f3n"], ["mediawiki.action.delete", "1onm3pb", [35, 252]], ["mediawiki.action.delete.file", "0jgvlvv", [35, 252]], ["mediawiki.action.edit", "080ptsv", [45, 141, 88, 136, 227]], ["mediawiki.action.edit.styles", "1upyuh9"], ["mediawiki.action.edit.collapsibleFooter", "01yyz3r", [36, 105, 117]], ["mediawiki.action.edit.preview",
    "0679d89", [39, 45, 88, 92, 158, 252]], ["mediawiki.action.history", "1xuxpjk", [36]], ["mediawiki.action.history.styles", "1j6le33"], ["mediawiki.action.view.dblClickEdit", "0gg8rmi", [130, 8]], ["mediawiki.action.view.metadata", "0hp4t9n", [154]], ["mediawiki.action.view.categoryPage.styles", "15inf8z"], ["mediawiki.action.view.postEdit", "1t4e33m", [158, 109]], ["mediawiki.action.view.redirect", "1dnfl8b", [24]], ["mediawiki.action.view.redirectPage", "1umb916"], ["mediawiki.action.view.rightClickEdit", "1cy6ddm"], ["mediawiki.action.edit.editWarning", "02kym79", [45, 90, 158]], ["mediawiki.action.view.filepage", "19ekogg"], ["mediawiki.language", "0aytd5t", [156]], ["mediawiki.cldr", "0nvnuvm", [157]], ["mediawiki.libs.pluralruleparser", "012f438"], ["mediawiki.jqueryMsg", "16avnyj", [155, 130, 8]], ["mediawiki.language.months", "0uah22e", [155]], ["mediawiki.language.names", "1ox21ac", [155]], ["mediawiki.language.specialCharacters", "1wf7ff3", [155]], ["mediawiki.libs.jpegmeta", "0ete22r"], ["mediawiki.page.gallery", "0okja0c", [46, 164]], [ "mediawiki.page.gallery.styles", "1xzwb22"], ["mediawiki.page.gallery.slideshow", "0oiwqo4", [88, 254, 271]], ["mediawiki.page.ready", "1k6p36m", [19, 22]], ["mediawiki.page.startup", "0xzy2gc"], ["mediawiki.page.patrol.ajax", "0oy6aa6", [39, 88, 110]], ["mediawiki.page.watch.ajax", "1wsigcl", [88, 158, 110]], ["mediawiki.page.rollback.confirmation", "0byxbcp", [27]], ["mediawiki.page.image.pagination", "1odkj3b", [39, 130]], ["mediawiki.rcfilters.filters.base.styles", "1dnor9q"], ["mediawiki.rcfilters.highlightCircles.seenunseen.styles", "09y3c3p"], ["mediawiki.rcfilters.filters.dm", "0ugltbu", [127, 158, 128, 248]], ["mediawiki.rcfilters.filters.ui", "1wvzne3", [36, 174, 222, 265, 267, 269, 271]], ["mediawiki.interface.helpers.styles", "0dfcni5"], ["mediawiki.special", "1v6bdq5"], ["mediawiki.special.apisandbox", "1dciskm", [36, 88, 158, 228, 251]], ["mediawiki.special.block", "17qbfx4", [100, 225, 239, 232, 240, 237, 265]], ["mediawiki.special.blocklist", "0ux469d"], ["mediawiki.special.changecredentials.js", "0yzqcla", [88, 102]], [ "mediawiki.special.changeslist", "00028c6"], ["mediawiki.special.changeslist.enhanced", "0vvwcca"], ["mediawiki.special.changeslist.legend", "1p9x93p"], ["mediawiki.special.changeslist.legend.js", "01hofsk", [36, 134]], ["mediawiki.special.contributions", "0od634l", [158, 225]], ["mediawiki.special.edittags", "1gdfgam", [23, 35]], ["mediawiki.special.import", "0ronzv8"], ["mediawiki.special.movePage", "0th966g", [222, 227]], ["mediawiki.special.pageLanguage", "0ghj2wt", [252]], ["mediawiki.special.preferences.ooui", "154t6wu", [90, 136, 111, 117, 232]], ["mediawiki.special.preferences.styles.ooui", "0y88bun"], ["mediawiki.special.recentchanges", "057bqh5"], ["mediawiki.special.revisionDelete", "0b05u16", [35]], ["mediawiki.special.search", "17beam7", [242]], ["mediawiki.special.search.commonsInterwikiWidget", "1k01nga", [127, 88, 158]], ["mediawiki.special.search.interwikiwidget.styles", "0a8v32b"], ["mediawiki.special.search.styles", "0d2knpy"], ["mediawiki.special.undelete", "086i3sd", [222, 227]], ["mediawiki.special.unwatchedPages",
    "1r4wu2z", [88, 110]], ["mediawiki.special.upload", "17e8woe", [39, 88, 90, 158, 162, 177, 84]], ["mediawiki.special.userlogin.common.styles", "1ks368f"], ["mediawiki.special.userlogin.login.styles", "0gmfrzp"], ["mediawiki.special.userlogin.signup.js", "0ncs64l", [88, 101, 158]], ["mediawiki.special.userlogin.signup.styles", "0clg5lh"], ["mediawiki.special.userrights", "0vj68sh", [35, 111]], ["mediawiki.special.watchlist", "1i67yg5", [88, 158, 110, 252]], ["mediawiki.special.version", "1e3nu61"], ["mediawiki.legacy.config", "008y1ag"], ["mediawiki.legacy.commonPrint", "078tnps"], ["mediawiki.legacy.protect", "0mdco4m", [35]], ["mediawiki.legacy.shared", "0cs9sjx"], ["mediawiki.legacy.oldshared", "0wx4dbt"], ["mediawiki.ui", "0zm5yu5"], ["mediawiki.ui.checkbox", "16waqko"], ["mediawiki.ui.radio", "0oyu6sq"], ["mediawiki.ui.anchor", "1z0300h"], ["mediawiki.ui.button", "02g5nwb"], ["mediawiki.ui.input", "1d9kubl"], ["mediawiki.ui.icon", "04f9vqr"], ["mediawiki.ui.text", "1h37dio"], ["mediawiki.widgets", "0gub4sa", [88, 223, 254]], [ "mediawiki.widgets.styles", "04ic2qu"], ["mediawiki.widgets.AbandonEditDialog", "1wgr129", [257]], ["mediawiki.widgets.DateInputWidget", "1dbwq4i", [226, 82, 254]], ["mediawiki.widgets.DateInputWidget.styles", "1vqcuus"], ["mediawiki.widgets.visibleLengthLimit", "09ljyc9", [35, 252]], ["mediawiki.widgets.datetime", "0rb5odq", [113, 252, 272, 273]], ["mediawiki.widgets.expiry", "0t9hlv6", [228, 82, 254]], ["mediawiki.widgets.CheckMatrixWidget", "19j4gxg", [252]], ["mediawiki.widgets.CategoryMultiselectWidget", "1gjdhsi", [96, 254]], ["mediawiki.widgets.SelectWithInputWidget", "1v42u7h", [233, 254]], ["mediawiki.widgets.SelectWithInputWidget.styles", "12dt6as"], ["mediawiki.widgets.SizeFilterWidget", "1wdugfj", [235, 254]], ["mediawiki.widgets.SizeFilterWidget.styles", "05wuevv"], ["mediawiki.widgets.MediaSearch", "1wh6v3l", [96, 254]], ["mediawiki.widgets.UserInputWidget", "06rva64", [88, 254]], ["mediawiki.widgets.UsersMultiselectWidget", "1xdpsc4", [88, 254]], ["mediawiki.widgets.NamespacesMultiselectWidget", "0z6c6d0", [254]], [ "mediawiki.widgets.TitlesMultiselectWidget", "1vc7c96", [222]], ["mediawiki.widgets.TagMultiselectWidget.styles", "1vu4oee"], ["mediawiki.widgets.SearchInputWidget", "0x0vjof", [116, 222]], ["mediawiki.widgets.SearchInputWidget.styles", "0fkv4nu"], ["mediawiki.widgets.StashedFileWidget", "0enae3n", [88, 252]], ["easy-deflate.core", "06fkmhu"], ["easy-deflate.deflate", "18qu8bw", [245]], ["easy-deflate.inflate", "1y4jg3r", [245]], ["oojs", "17r0vy2"], ["mediawiki.router", "045fw5w", [250]], ["oojs-router", "1rw732c", [248]], ["oojs-ui", "07j6l8d", [256, 254, 257]], ["oojs-ui-core", "0nmrizr", [155, 248, 253, 261, 262, 268, 258, 259]], ["oojs-ui-core.styles", "1ebkuml"], ["oojs-ui-widgets", "15o5q4l", [252, 263, 272, 273]], ["oojs-ui-widgets.styles", "1w19unn"], ["oojs-ui-toolbars", "1vxhp4x", [252, 273]], ["oojs-ui-windows", "0chxd97", [252, 273]], ["oojs-ui.styles.indicators", "0z80a1m"], ["oojs-ui.styles.textures", "09ny7q5"], ["oojs-ui.styles.icons-accessibility", "0ulbhpa"], ["oojs-ui.styles.icons-alerts", "1v1ou9c"], ["oojs-ui.styles.icons-content",
    "1tt1k83"], ["oojs-ui.styles.icons-editing-advanced", "1u7qd3x"], ["oojs-ui.styles.icons-editing-citation", "10g4z7r"], ["oojs-ui.styles.icons-editing-core", "1inhh9z"], ["oojs-ui.styles.icons-editing-list", "1bozhl4"], ["oojs-ui.styles.icons-editing-styling", "1t0cx1a"], ["oojs-ui.styles.icons-interactions", "04gpen8"], ["oojs-ui.styles.icons-layout", "0w6om6u"], ["oojs-ui.styles.icons-location", "029luuy"], ["oojs-ui.styles.icons-media", "1eoijio"], ["oojs-ui.styles.icons-moderation", "1xdxk8d"], ["oojs-ui.styles.icons-movement", "04zuk4u"], ["oojs-ui.styles.icons-user", "1sjyngz"], ["oojs-ui.styles.icons-wikimedia", "19m4wb8"], ["skins.vector.styles", "1kp99k8"], ["skins.vector.styles.responsive", "1qzk5l1"], ["skins.vector.js", "1va25sr", [42, 46]], ["skins.monobook.styles", "0uh45ne"], ["skins.monobook.responsive", "0j9p7fo"], ["skins.monobook.mobile", "076bk3p", [130]], ["skins.modern", "03tbnt5"], ["skins.cologneblue", "0bxburf"], ["skins.timeless", "1iz0uif"], ["skins.timeless.misc", "179u8dz"], ["skins.timeless.js", "1pw39ki",
    [42]], ["skins.timeless.mobile", "0d3cjjj"], ["ext.timeline.styles", "1fzotet"], ["ext.wikihiero", "11mrb9o"], ["ext.wikihiero.Special", "0z4ias2", [39, 252]], ["ext.wikihiero.visualEditor", "1h9c5cw", [503]], ["ext.charinsert", "0d5dyei", [45]], ["ext.charinsert.styles", "1111tzt"], ["ext.cite.styles", "1h7fnty"], ["ext.cite.a11y", "1xr4gfo"], ["ext.cite.ux-enhancements", "1fpabe3"], ["ext.cite.style", "03vcvp9"], ["ext.citeThisPage", "00jpoxj"], ["ext.inputBox.styles", "15iisyf"], ["ext.inputBox", "02hkw2a", [46]], ["ext.pygments", "0gcc6jx"], ["ext.categoryTree", "09h0yyf", [88]], ["ext.categoryTree.styles", "0yd67vv"], ["ext.spamBlacklist.visualEditor", "1yuow70"], ["mediawiki.api.titleblacklist", "1bfs7sn", [88]], ["ext.titleblacklist.visualEditor", "17t066t"], ["mw.PopUpMediaTransform", "0z1uwcl", [118, 326, 308]], ["mw.PopUpMediaTransform.styles", "0b1w3e6"], ["mw.TMHGalleryHook.js", "1j3dxsi"], ["ext.tmh.embedPlayerIframe", "1wug44e", [343, 326]], ["mw.MediaWikiPlayerSupport", "19up8gm", [342, 326]], ["mw.MediaWikiPlayer.loader", "0jw9hl9", [ 344, 359]], ["ext.tmh.video-js", "187ophc"], ["ext.tmh.videojs-ogvjs", "042rwdp", [324, 313]], ["ext.tmh.videojs-resolution-switcher", "1s4u1ml", [313]], ["ext.tmh.videojs-responsive-layout", "0j7s41m", [313]], ["ext.tmh.mw-info-button", "02zc114", [313, 118]], ["ext.tmh.player", "07ov0bu", [323, 317, 315, 316]], ["ext.tmh.player.styles", "0qhm0fl"], ["ext.tmh.thumbnail.styles", "0o7wzuw"], ["ext.tmh.transcodetable", "07ayexw", [88, 251]], ["ext.tmh.TimedTextSelector", "0ugvi00"], ["ext.tmh.OgvJsSupport", "1krju4u"], ["ext.tmh.OgvJs", "1qhqm17", [323]], ["embedPlayerIframeStyle", "1sk2fi1"], ["mw.MwEmbedSupport", "0vhhfqq", [327, 329, 339, 338, 330]], ["Spinner", "1sbnoxq", [130]], ["iScroll", "0ir0rjs"], ["jquery.loadingSpinner", "188pg4b"], ["mw.MwEmbedSupport.style", "0y5tqhk"], ["mediawiki.UtilitiesTime", "13npaan"], ["mediawiki.client", "0gb0j5c"], ["mediawiki.absoluteUrl", "05v5052", [127]], ["mw.ajaxProxy", "0ygq26o"], ["fullScreenApi", "1r9pf85"], ["jquery.embedMenu", "1xnm22b"], ["jquery.ui.touchPunch", "1df4yrj", [48, 58]], [ "jquery.triggerQueueCallback", "167pvcf"], ["jquery.mwEmbedUtil", "162zyg8"], ["jquery.debouncedresize", "0jlnu52"], ["mw.Language.names", "0yjsgg8"], ["mw.Api", "06p6228"], ["jquery.embedPlayer", "16la9rh"], ["mw.EmbedPlayer.loader", "1s5d0zc", [343]], ["mw.MediaElement", "04eg6bj", [323]], ["mw.MediaPlayer", "1xkwfrc"], ["mw.MediaPlayers", "0vw8oxj", [346]], ["mw.MediaSource", "1lhyip0", [326]], ["mw.EmbedTypes", "0fssbxg", [127, 347]], ["mw.EmbedPlayer", "1n7nxmi", [335, 28, 340, 336, 33, 63, 337, 331, 333, 332, 158, 353, 349, 345, 348]], ["mw.EmbedPlayerKplayer", "0tnm63k"], ["mw.EmbedPlayerGeneric", "12b5amx"], ["mw.EmbedPlayerNative", "1pd8vi0"], ["mw.EmbedPlayerVLCApp", "0wwkt1s", [127]], ["mw.EmbedPlayerIEWebMPrompt", "0316p9t"], ["mw.EmbedPlayerOgvJs", "01ijdjh", [323, 39]], ["mw.EmbedPlayerImageOverlay", "00o6a84"], ["mw.EmbedPlayerVlc", "0tna0of"], ["mw.TimedText.loader", "05p6j6f"], ["mw.TimedText", "0w88z6d", [350, 361]], ["mw.TextSource", "11tn001", [331, 334]], ["ext.urlShortener.special", "0qb16wj", [127, 88, 102, 251]], ["ext.urlShortener.toolbar",
    "1pp0x1b", [88]], ["ext.securepoll.htmlform", "1yziwg4"], ["ext.securepoll", "05h4ncg"], ["ext.score.visualEditor", "0fmzhwe", [367, 503]], ["ext.score.visualEditor.icons", "0cevyjl"], ["ext.score.popup", "16ptf9c", [88]], ["ext.nuke.confirm", "03xbiq6", [158]], ["ext.confirmEdit.editPreview.ipwhitelist.styles", "0loke3f"], ["ext.confirmEdit.visualEditor", "1g3cjgq"], ["ext.confirmEdit.simpleCaptcha", "1s1aepv"], ["ext.confirmEdit.fancyCaptcha.styles", "1467l1o"], ["ext.confirmEdit.fancyCaptcha", "14cu0dr", [88]], ["ext.confirmEdit.fancyCaptchaMobile", "14cu0dr", [563]], ["ext.centralauth", "1gsop4q", [39, 130]], ["ext.centralauth.centralautologin", "0vg1hy5", [158, 110]], ["ext.centralauth.centralautologin.clearcookie", "1ixgi20"], ["ext.centralauth.noflash", "1cud6n1"], ["ext.centralauth.globaluserautocomplete", "0v2f052", [41, 88]], ["ext.centralauth.globalusers", "17g5f1n"], ["ext.centralauth.globalgrouppermissions", "14vr42l"], ["ext.centralauth.globalrenameuser", "1e7cpvo", [130]], ["ext.centralauth.globalrenameuser.styles", "1fsrqx0"
    ], ["ext.centralauth.ForeignApi", "1344nh3", [97]], ["ext.widgets.GlobalUserInputWidget", "10mkktr", [88, 254]], ["ext.GlobalUserPage", "1y49yr4"], ["ext.apifeatureusage", "1aurd32"], ["ext.dismissableSiteNotice", "0y84caw", [28, 130]], ["ext.dismissableSiteNotice.styles", "1xxj6o7"], ["jquery.ui.multiselect", "0qwr195", [56, 64, 158]], ["ext.centralNotice.adminUi", "108ant9", [53, 391, 127]], ["ext.centralNotice.adminUi.campaignPager", "1cwyb0x"], ["ext.centralNotice.adminUi.bannerManager", "1t0494c", [392, 54]], ["ext.centralNotice.adminUi.bannerEditor", "1cpvn2h", [392, 54, 88]], ["ext.centralNotice.adminUi.campaignManager", "1mosm83", [392, 46, 54, 63, 85, 251]], ["ext.centralNotice.startUp", "17iws46", [399]], ["ext.centralNotice.geoIP", "1svqygb", [28]], ["ext.centralNotice.choiceData", "1bbpvot", [403, 404, 405, 406]], ["ext.centralNotice.display", "1tw8vil", [398, 401, 127]], ["ext.centralNotice.kvStore", "0un16jc", [402]], ["ext.centralNotice.kvStoreMaintenance", "1vmne68"], ["ext.centralNotice.bannerHistoryLogger", "18ci9bz", [400, 128]], [ "ext.centralNotice.impressionDiet", "1753l4t", [400]], ["ext.centralNotice.largeBannerLimit", "0fybqa8", [400, 134]], ["ext.centralNotice.legacySupport", "0wctcxy", [400]], ["ext.centralNotice.bannerSequence", "131xgje", [400]], ["ext.centralNotice.adminUi.bannerSequence", "05nfss7", [396]], ["ext.centralNotice.freegeoipLookup", "0orp3pu", [398]], ["ext.centralNotice.bannerController", "07j6l8d", [397]], ["ext.centralNotice.impressionEventsSampleRate", "1p3o9dw", [400]], ["ext.centralNotice.cspViolationAlert", "0t9drj5"], ["ext.wikimediamessages.contactpage.affcomusergroup", "11s47hr"], ["mediawiki.special.block.feedback.request", "16f3699"], ["ext.ElectronPdfService.print.styles", "1oi9lin"], ["ext.ElectronPdfService.special.styles", "050udvz"], ["ext.ElectronPdfService.special.selectionImages", "0tb1c4g"], ["ext.advancedSearch.initialstyles", "02jqo4a"], ["ext.advancedSearch.styles", "12jzaf5"], ["ext.advancedSearch.searchtoken", "1v7b7kh", [], "private"], ["ext.advancedSearch.elements", "0ft3qly", [419, 127, 158, 128, 254, 269]], [ "ext.advancedSearch.init", "1bvkx4c", [421, 420]], ["ext.advancedSearch.SearchFieldUI", "1yuzuie", [118, 254]], ["ext.abuseFilter", "02csl1n"], ["ext.abuseFilter.edit", "04macgs", [39, 45, 88, 90, 254]], ["ext.abuseFilter.tools", "0xjop6f", [39, 88, 110]], ["ext.abuseFilter.examine", "1sqjdeb", [39, 88]], ["ext.abuseFilter.ace", "1774c6y", [654]], ["ext.abuseFilter.visualEditor", "13k2o29"], ["ext.wikiEditor", "02t9nk4", [28, 41, 42, 45, 46, 54, 124, 122, 161, 264, 265, 266, 267, 271, 84], "ext.wikiEditor"], ["ext.wikiEditor.styles", "19xd3w4", [], "ext.wikiEditor"], ["ext.CodeMirror", "07q8qhv", [433, 45, 61, 128, 267]], ["ext.CodeMirror.data", "14q0waw"], ["ext.CodeMirror.lib", "0he9210"], ["ext.CodeMirror.mode.mediawiki", "12p0ggr", [434]], ["ext.CodeMirror.lib.mode.css", "04btbgn", [434]], ["ext.CodeMirror.lib.mode.javascript", "14qodga", [434]], ["ext.CodeMirror.lib.mode.xml", "0zcg1yk", [434]], ["ext.CodeMirror.lib.mode.htmlmixed", "1si6ffd", [436, 437, 438]], ["ext.CodeMirror.lib.mode.clike", "17t96nl", [434]], ["ext.CodeMirror.lib.mode.php", "112538x", [440, 439]], ["ext.CodeMirror.visualEditor.init", "1gl5ue9"], ["ext.CodeMirror.visualEditor", "0u73j6n", [433, 435, 503]], ["ext.TemplateWizard", "0ih1ns8", [45, 222, 225, 237, 256, 257]], ["ext.acw.eventlogging", "1ma8bqr"], ["ext.acw.landingPageStyles", "1vt7x4k"], ["ext.MassMessage.autocomplete", "0ohn4s9", [51]], ["ext.MassMessage.special.js", "0zo7you", [447, 35, 46, 158]], ["ext.MassMessage.special", "0jlyx02"], ["ext.MassMessage.content", "1rgmamg"], ["ext.MassMessage.content.js", "0parv1c", [447, 27, 88]], ["ext.MassMessage.content.noedit", "0o0107u"], ["ext.MassMessage.content.nojs", "0sd5pzt"], ["ext.MassMessage.create", "042vn51", [447, 102, 158]], ["ext.MassMessage.edit", "1js5cfj", [227, 251]], ["ext.betaFeatures", "0slnf2z", [24]], ["ext.betaFeatures.styles", "1oux37o"], ["mmv", "1gr49z0", [25, 30, 46, 47, 127, 158, 463]], ["mmv.ui.ondemandshareddependencies", "0am30dm", [458, 251]], ["mmv.ui.download.pane", "19vg33x", [214, 459]], ["mmv.ui.reuse.shareembed", "06w5p1e", [459]], ["mmv.ui.tipsyDialog", "1w4h6dw", [458]], ["mmv.bootstrap", "06so1d3", [110, 218, 220,
    465, 248]], ["mmv.bootstrap.autostart", "1ligw78", [463]], ["mmv.head", "1ihejqs", [128]], ["ext.popups.images", "1t935w0"], ["ext.popups", "0mpkta5"], ["ext.popups.main", "1czxrl6", [466, 127, 135, 158, 218, 220, 128]], ["ext.linter.edit", "0exbhqp", [45]], ["socket.io", "0nv0jim"], ["dompurify", "1ckbvin"], ["color-picker", "1hua6r9"], ["unicodejs", "1qvy4e3"], ["papaparse", "13ih3e7"], ["rangefix", "01bu8pa"], ["spark-md5", "1icwqfv"], ["ext.visualEditor.supportCheck", "1ra37ny"], ["ext.visualEditor.sanitize", "08q61wv", [471, 492]], ["ext.visualEditor.progressBarWidget", "0bs7eer"], ["ext.visualEditor.tempWikitextEditorWidget", "1byhzpj", [136, 128]], ["ext.visualEditor.desktopArticleTarget.init", "1ok6goh", [479, 477, 480, 491, 28, 45, 127, 167]], ["ext.visualEditor.desktopArticleTarget.noscript", "095iz8w"], ["ext.visualEditor.targetLoader", "06anbe2", [491, 45, 127, 128]], ["ext.visualEditor.desktopTarget", "0vyh6hf"], ["ext.visualEditor.desktopArticleTarget", "150jrzb", [495, 500, 484, 505]], ["ext.visualEditor.collabTarget", "1ynkte6", [493, 499, 269]], [ "ext.visualEditor.collabTarget.desktop", "0gk3ctu", [486, 500, 484, 505]], ["ext.visualEditor.collabTarget.init", "15fqu11", [477, 222, 251]], ["ext.visualEditor.collabTarget.init.styles", "12d0edh"], ["ext.visualEditor.ve", "0up6ejb"], ["ext.visualEditor.track", "0b1o38l", [490]], ["ext.visualEditor.base", "1qv4krb", [490, 251, 473]], ["ext.visualEditor.mediawiki", "1s0w44c", [492, 483, 735]], ["ext.visualEditor.mwsave", "00ag2ux", [503, 35]], ["ext.visualEditor.articleTarget", "122idtm", [504, 494, 224]], ["ext.visualEditor.data", "12b8tym", [493]], ["ext.visualEditor.core", "1988khn", [492, 477, 24, 474, 475, 476]], ["ext.visualEditor.commentAnnotation", "0zx1u5f", [497]], ["ext.visualEditor.rebase", "13na1j3", [472, 514, 498, 478, 274, 470]], ["ext.visualEditor.core.desktop", "0m77mz3", [497]], ["ext.visualEditor.welcome", "0feptmd", [251]], ["ext.visualEditor.switching", "0w1g89k", [251, 260, 265]], ["ext.visualEditor.mwcore", "0h9k7qr", [515, 493, 1242, 502, 501, 28, 92, 158, 110, 14, 222]], ["ext.visualEditor.mwextensions", "07j6l8d", [496, 526, 519, 521, 506, 523,
    508, 520, 509, 511]], ["ext.visualEditor.mwextensions.desktop", "07j6l8d", [504, 510, 124]], ["ext.visualEditor.mwformatting", "0pxntf4", [503]], ["ext.visualEditor.mwimage.core", "131ot5u", [503]], ["ext.visualEditor.mwimage", "0zjldbi", [507, 236, 82, 271, 275]], ["ext.visualEditor.mwlink", "0ydbhdz", [503]], ["ext.visualEditor.mwmeta", "0g19z08", [509, 151]], ["ext.visualEditor.mwtransclusion", "0zfoebs", [503, 237]], ["treeDiffer", "0mr1wpq"], ["diffMatchPatch", "179lnrj"], ["ext.visualEditor.checkList", "1hmptc7", [497]], ["ext.visualEditor.diffing", "05o1lai", [513, 497, 512]], ["ext.visualEditor.diffPage.init.styles", "1appxmn"], ["ext.visualEditor.diffLoader", "0if5c07", [483]], ["ext.visualEditor.diffPage.init", "0uo4oq1", [517, 251, 260]], ["ext.visualEditor.language", "1uoiorm", [497, 735, 160]], ["ext.visualEditor.mwlanguage", "1hib9kz", [497]], ["ext.visualEditor.mwalienextension", "0j01wcv", [503]], ["ext.visualEditor.mwwikitext", "1twse0b", [509, 136]], ["ext.visualEditor.mwgallery", "1b9lxka", [503, 164, 236, 271]], [ "ext.visualEditor.mwsignature", "1p79byq", [511]], ["ext.visualEditor.experimental", "07j6l8d"], ["ext.visualEditor.icons", "07j6l8d", [527, 528, 261, 262, 263, 265, 266, 267, 268, 269, 272, 273, 274, 258, 259]], ["ext.visualEditor.moduleIcons", "0su0ogq"], ["ext.visualEditor.moduleIndicators", "09mo5xt"], ["ext.citoid.visualEditor", "0ik0ivt", [1131, 530]], ["ext.citoid.visualEditor.data", "1883rhy", [493]], ["ext.templateData", "0gsonyt"], ["ext.templateDataGenerator.editPage", "1rhlu2q"], ["ext.templateDataGenerator.editTemplatePage", "0uqbz3t", [535, 88]], ["ext.templateDataGenerator.data", "1rb7msv", [248]], ["ext.templateDataGenerator.ui", "1iwk46c", [531, 536, 534, 537, 45, 735, 254, 257, 269]], ["ext.templateData.images", "1q0cy5e"], ["ext.templateDataGenerator.ui.images", "15wasbg"], ["ext.wikiLove.icon", "1uyxx4t"], ["ext.wikiLove.startup", "1ivd7kd", [54, 88, 218]], ["ext.wikiLove.local", "0th34b2"], ["ext.wikiLove.init", "1fsfbk0", [539]], ["mediawiki.libs.guiders", "0d3kkd0"], ["ext.guidedTour.styles", "1al4xqt", [542, 218]], [ "ext.guidedTour.lib.internal", "1g444y6"], ["ext.guidedTour.lib", "1fkgu88", [544, 543, 134, 158, 1259, 1262, 1261, 1258, 1257, 1260]], ["ext.guidedTour.launcher", "00qlrsu"], ["ext.guidedTour", "1sl9bqk", [545]], ["ext.guidedTour.tour.firstedit", "149itd6", [547]], ["ext.guidedTour.tour.test", "0nnjtds", [547]], ["ext.guidedTour.tour.onshow", "1hneekl", [547]], ["ext.guidedTour.tour.uprightdownleft", "07dsn0i", [547]], ["mobile.app", "0flzhpc"], ["mobile.app.parsoid", "1st20xa"], ["mobile.pagelist.styles", "0ydu7v0"], ["mobile.pagesummary.styles", "1r07ajl"], ["mobile.startup.images.variants", "04mto3s"], ["mobile.messageBox.styles", "0np6zzq"], ["mobile.userpage.icons", "0itj30l"], ["mobile.userpage.styles", "1qfr6yj"], ["mediawiki.template.hogan", "0u3vvhw", [85]], ["mobile.startup.images", "1ngwahj"], ["mobile.init", "0xwfhhd", [127, 135, 563]], ["mobile.startup", "0zio5wc", [46, 158, 110, 249, 560, 218, 220, 128, 131, 557, 554, 555, 561, 556]], ["mobile.editor.overlay", "14gc7d1", [90, 136, 109, 219, 224, 565, 563, 251, 265]], ["mobile.editor.images", "04elcwk"], [ "mobile.talk.overlays", "0vn2fyd", [217, 564]], ["mobile.mediaViewer", "12qmob6", [563]], ["mobile.categories.overlays", "0kpviy3", [564]], ["mobile.languages.structured", "1ih114f", [563]], ["mobile.nearby.images", "1tfd5pz"], ["mobile.special.user.icons", "1wpegbb"], ["mobile.special.mobileoptions.styles", "0nwc663"], ["mobile.special.mobileoptions.scripts", "0lne2q9", [563]], ["mobile.special.nearby.styles", "12ibrrv"], ["mobile.special.userlogin.scripts", "11ahr9b"], ["mobile.special.nearby.scripts", "11qo0ys", [127, 570, 574, 563]], ["mobile.special.uploads.scripts", "1bs1c14", [563]], ["mobile.special.mobilediff.images", "060uf1d"], ["skins.minerva.base.styles", "07f9gcp"], ["skins.minerva.content.styles", "1y0r6d4"], ["skins.minerva.content.styles.images", "0c95djg"], ["skins.minerva.icons.loggedin", "1306fgr"], ["skins.minerva.amc.styles", "0pqhg32"], ["wikimedia.ui", "1watpeq"], ["skins.minerva.icons.images", "0xxwza4"], ["skins.minerva.icons.images.scripts", "07j6l8d", [587, 589, 590, 588]], [ "skins.minerva.icons.images.scripts.misc", "063tw4c"], ["skins.minerva.icons.page.issues.uncolored", "1rm6gdn"], ["skins.minerva.icons.page.issues.default.color", "196smnj"], ["skins.minerva.icons.page.issues.medium.color", "0828zg1"], ["skins.minerva.mainPage.styles", "0xudvep"], ["skins.minerva.userpage.icons", "168yrnx"], ["skins.minerva.userpage.styles", "1g9u7iz"], ["skins.minerva.mainMenu.icons", "066usz1"], ["skins.minerva.mainMenu.styles", "0wr782j"], ["skins.minerva.loggedin.styles", "0p22uy7"], ["skins.minerva.scripts", "0cg36bf", [28, 562, 586, 594, 595, 584]], ["skins.minerva.notifications.badge", "1bxncsq", [563]], ["skins.minerva.notifications", "1kfpvz7", [217, 598, 597]], ["skins.minerva.options.share.icon", "1vnh49c"], ["skins.minerva.options", "0m99li3", [600, 597]], ["skins.minerva.talk", "0co02m5", [597]], ["skins.minerva.toggling", "0oaddwy", [597]], ["skins.minerva.watchstar", "0vr67yj", [597]], ["zerobanner.config.styles", "1fi8o41"], ["ext.math.styles", "15krita"], ["ext.math.scripts", "1uk5zyq"], [ "ext.math.visualEditor", "18fl2d9", [606, 503]], ["ext.math.visualEditor.mathSymbolsData", "0ck829m", [608]], ["ext.math.visualEditor.mathSymbols", "0timzyu", [609]], ["ext.math.visualEditor.chemSymbolsData", "0ar9ku9", [608]], ["ext.math.visualEditor.chemSymbols", "12fgj85", [611]], ["ext.babel", "019fixp"], ["ext.vipsscaler", "1yy9byh", [615]], ["jquery.ucompare", "0n5cf3z"], ["mediawiki.template.underscore", "04nm6ui", [617, 84]], ["ext.pageTriage.external", "18hx74z"], ["jquery.badge.external", "0a62j20"], ["ext.pageTriage.init", "1cjhroj", [617]], ["ext.pageTriage.util", "0w9j36h", [619]], ["ext.pageTriage.models", "068eneb", [619, 127, 128]], ["jquery.tipoff", "1f7lttn"], ["ext.pageTriage.views.list", "165hcv0", [621, 620, 39, 622, 52, 158, 616]], ["ext.pageTriage.defaultTagsOptions", "1il2s2h"], ["ext.pageTriage.externalTagsOptions", "022h6x3", [624]], ["ext.pageTriage.defaultDeletionTagsOptions", "1cqlxka", [118]], ["ext.pageTriage.externalDeletionTagsOptions", "0glogko", [626]], ["ext.pageTriage.toolbarStartup", "0sw7r96"], [ "ext.pageTriage.article", "0yulkyf", [619, 127, 88, 158]], ["ext.interwiki.specialpage", "1g0ze4s"], ["ext.echo.logger", "0n8m3ii", [248]], ["ext.echo.ui.desktop", "0fyasqf", [638, 633]], ["ext.echo.ui", "09teuy8", [634, 631, 1247, 639, 158, 110, 254, 274]], ["ext.echo.dm", "1szgvtq", [637, 82]], ["ext.echo.api", "18t9sos", [96]], ["ext.echo.base", "07j6l8d", [631]], ["ext.echo.init", "0q7jihy", [635, 127]], ["ext.echo.styles.badge", "07gi8m7"], ["ext.echo.styles.notifications", "1a0j95f"], ["ext.echo.styles.alert", "15vzabx"], ["ext.echo.special", "0ye5jmv", [642, 633]], ["ext.echo.styles.special", "1rx3zqn"], ["ext.echo.badgeicons", "04lr8br"], ["ext.thanks.images", "1k5x0d7"], ["ext.thanks", "0nuis2b", [28, 88]], ["ext.thanks.corethank", "0lw0vun", [645, 27, 257]], ["ext.thanks.mobilediff", "0zhcrk2", [644, 88, 158, 110]], ["ext.thanks.jquery.findWithParent", "01jel51"], ["ext.thanks.flowthank", "004whf0", [645, 648, 158, 257]], ["ext.disambiguator.visualEditor", "0i5gvgy", [510]], ["ext.codeEditor", "1lfdbid", [652], "ext.wikiEditor"], ["jquery.codeEditor",
    "0l4qalh", [654, 653, 430, 134], "ext.wikiEditor"], ["ext.codeEditor.icons", "0lj720h"], ["ext.codeEditor.ace", "0dx2f4j", [], "ext.codeEditor.ace"], ["ext.codeEditor.ace.modes", "1fmy9rw", [654], "ext.codeEditor.ace"], ["ext.scribunto.errors", "1t42pcx", [54]], ["ext.scribunto.logs", "1md8iza"], ["ext.scribunto.edit", "0h0napz", [39, 88]], ["ext.guidedTour.tour.gettingstartedtasktoolbar", "03qbpqk", [664, 547]], ["ext.gettingstarted.lightbulb.postEdit", "08tqm72", [665, 663, 1273]], ["ext.gettingstarted.lightbulb.personalTools", "1wha2rd"], ["ext.gettingstarted.lightbulb.flyout", "0b9zm2x", [665, 663, 543, 1273, 1274]], ["ext.gettingstarted.lightbulb.common", "0hd4u6z", [664, 82, 1271, 1272]], ["ext.gettingstarted.logging", "0zupwb2", [28, 149, 128]], ["ext.gettingstarted.api", "1jpphw8", [88]], ["ext.gettingstarted.taskToolbar", "11gsah8", [665, 664, 545]], ["ext.gettingstarted.return", "057svr3", [665, 664, 545, 127, 1267]], ["ext.relatedArticles.cards", "14fk1sh", [669, 130, 248]], ["ext.relatedArticles.lib", "0qgn19e"], [ "ext.relatedArticles.readMore.gateway", "0yfpwse", [248]], ["ext.relatedArticles.readMore.bootstrap", "1ogw21h", [670, 46, 127, 135, 128, 131]], ["ext.relatedArticles.readMore", "1hrtgus", [130]], ["ext.RevisionSlider.lazyCss", "0jobzq6"], ["ext.RevisionSlider.lazyJs", "0dx6uof", [679, 273]], ["ext.RevisionSlider.init", "08rv6o6", [678, 687, 679, 684, 158]], ["ext.RevisionSlider.noscript", "0lxnpeq"], ["ext.RevisionSlider.util", "1nasf6q"], ["ext.RevisionSlider.Api", "0oxpnqq"], ["ext.RevisionSlider.Settings", "0u3ob5r", [134, 128]], ["ext.RevisionSlider.Revision", "1iz5nl2", [82]], ["ext.RevisionSlider.Pointer", "1ed41sa", [683, 682]], ["ext.RevisionSlider.PointerView", "1skxcp0"], ["ext.RevisionSlider.PointerLine", "0bia6ag"], ["ext.RevisionSlider.Slider", "028fh6f", [685]], ["ext.RevisionSlider.SliderView", "1u83c1x", [686, 689, 681, 677, 55]], ["ext.RevisionSlider.DiffPage", "08hhxk1", [127]], ["ext.RevisionSlider.RevisionList", "0sw867c", [680, 688]], ["ext.RevisionSlider.RevisionListView", "05q3hb1", [130, 251]], ["ext.RevisionSlider.HelpDialog",
    "1xr7cs2", [690, 251]], ["ext.RevisionSlider.dialogImages", "10l201t"], ["ext.TwoColConflict.InlineCss", "0omerqs"], ["ext.TwoColConflict.Inline.initJs", "1gv1114", [695, 696, 693]], ["ext.TwoColConflict.Settings", "0a11c78", [134, 128]], ["ext.TwoColConflict.Inline.filterOptionsJs", "0wu13u5", [695, 251]], ["ext.TwoColConflict.Inline.AutoScroll", "1op7es2"], ["ext.TwoColConflict.Inline.HelpDialog", "15avgwa", [698, 251]], ["ext.TwoColConflict.Inline.HelpDialogCss", "0bqmuym"], ["ext.TwoColConflict.Inline.HelpDialogImages", "0intav7"], ["ext.TwoColConflict.SpecialConflictTestPageCss", "0o0bywr"], ["ext.TwoColConflict.SplitJs", "07dwjvk", [704, 703]], ["ext.TwoColConflict.SplitCss", "0isfbnt"], ["ext.TwoColConflict.Split.TourImages", "1yzxqh9"], ["ext.TwoColConflict.Split.Tour", "0vupgjc", [693, 702, 251]], ["ext.TwoColConflict.Split.Merger", "0y27d6z"], ["ext.eventLogging", "1yxz8eh", [128]], ["ext.eventLogging.subscriber", "07j6l8d", [705]], ["ext.eventLogging.debug", "1m9y9el"], ["ext.eventLogging.jsonSchema", "090skt6"], [ "ext.eventLogging.jsonSchema.styles", "1b0fi5r"], ["ext.wikimediaEvents", "1swq43z", [706, 127, 135]], ["ext.wikimediaEvents.loggedin", "1og6bn0", [127, 128]], ["ext.wikimediaEvents.wikibase", "0pgbmvr"], ["ext.navigationTiming", "0fxkr6u", [705, 28]], ["ext.navigationTiming.rumSpeedIndex", "07tpatv"], ["ext.uls.common", "1w9ul9h", [735, 134, 128]], ["ext.uls.compactlinks", "1629d1o", [720, 158, 218]], ["ext.uls.geoclient", "1j666c5", [134]], ["ext.uls.i18n", "16vhe8z", [34, 130]], ["ext.uls.ime", "1r3w0k4", [726, 1322, 733, 110]], ["ext.uls.init", "07j6l8d", [715]], ["ext.uls.inputsettings", "16nupor", [719, 725, 216]], ["ext.uls.interface", "0jlwfd7", [730, 158]], ["ext.uls.interlanguage", "1f5yooe"], ["ext.uls.languagenames", "147q3qo"], ["ext.uls.languagesettings", "1junyrw", [727, 1322, 736, 218]], ["ext.uls.mediawiki", "07qofw8", [715, 724, 727, 734]], ["ext.uls.messages", "0p09o3q", [718]], ["ext.uls.preferencespage", "1fgyyft"], ["ext.uls.pt", "0240gr1"], ["ext.uls.webfonts", "16mv5uz", [715, 1322]], ["ext.uls.webfonts.fonts", "07j6l8d", [732, 737]], [ "ext.uls.webfonts.repository", "1asfc8z"], ["jquery.ime", "0nvozfg"], ["jquery.uls", "1kttrbc", [34, 735, 736]], ["jquery.uls.data", "03xqo3h"], ["jquery.uls.grid", "1hn4j7e"], ["jquery.webfonts", "0ofxjie"], ["rangy.core", "1y772rp"], ["ext.cx.contributions", "1jsn4c9", [130, 252, 263]], ["ext.cx.model", "0vh0rme"], ["ext.cx.feedback", "1uvkzrv", [740]], ["ext.cx.dashboard", "0c6sph6", [748, 741, 793, 781, 820, 823, 271]], ["ext.cx.util", "1jae4wi", [740]], ["mw.cx.util", "0r9ka4n", [740, 128]], ["ext.cx.util.selection", "1x88t94", [740]], ["ext.cx.sitemapper", "0wihrt6", [740, 96, 127, 134, 128]], ["ext.cx.source", "1yxp0qh", [743, 791, 735, 127, 158, 14, 128]], ["ext.cx.SourcePageSelector", "0j4o8fj", [749, 845]], ["ext.cx.SelectedSourcePage", "1jgstxv", [789, 41, 750]], ["mw.cx.ui.LanguageFilter", "00jhgzc", [726, 158, 218, 799, 744]], ["ext.cx.translation", "02pwemd", [789, 752, 745, 735]], ["ext.cx.translation.progress", "0lu4buk", [743]], ["ext.cx.tools.manager", "1o6gctb"], ["ext.cx.tools", "02mawcr", [741, 772, 771, 760, 759, 769, 768, 756, 761, 758, 763, 757, 770, 764, 765, 766,
    767, 745, 791]], ["ext.cx.tools.card", "07evpve"], ["ext.cx.tools.instructions", "0gui5mg", [755, 753, 158]], ["ext.cx.tools.mtabuse", "1x7xqox", [740, 755, 753]], ["ext.cx.tools.linter", "0ii4luo", [755, 753]], ["ext.cx.tools.formatter", "0hgw3if", [755, 753]], ["ext.cx.tools.dictionary", "0xnljsv", [755, 753]], ["ext.cx.tools.link", "02ktj3h", [755, 753, 735, 88]], ["ext.cx.tools.mt", "09rxmgh", [743, 117]], ["ext.cx.tools.mt.card", "0ckp3jj", [755, 753, 762]], ["ext.cx.tools.reference", "13t1gsf", [755, 753, 743]], ["ext.cx.tools.template", "1j7m0io", [740, 113, 254]], ["ext.cx.tools.template.card", "00mk63k", [755, 753, 743]], ["ext.cx.tools.template.editor", "0lq72fv", [743, 790, 841]], ["ext.cx.tools.images", "19a9mjp"], ["ext.cx.tools.gallery", "0ke7yj1"], ["ext.cx.tools.poem", "12f2czc"], ["ext.cx.tools.categories", "0n0o7nf", [746]], ["ext.cx.progressbar", "1nf065n", [158]], ["ext.cx.translation.loader", "08gws9n", [740, 128]], ["ext.cx.translation.storage", "0gl9b6r", [246, 88]], ["ext.cx.publish", "0jumm0c", [246, 777, 789]], ["ext.cx.wikibase.link", "0mbx4ss"], ["ext.cx.publish.dialog", "1jfetio", [746, 158]], ["ext.cx.eventlogging", "0eonv82", [740, 128]], ["ext.cx.interlanguagelink", "0ew7l3h", [746, 743, 720, 158]], ["ext.cx.entrypoint", "0k4ta4u", [746, 792, 735, 158]], ["mw.cx.dashboard.lists", "0ywhhmk", [772, 743, 791, 222, 82, 750, 265]], ["ext.cx.translation.conflict", "0oubcqm", [158]], ["ext.cx.stats", "0eqlmx6", [784, 746, 743, 792, 791, 735, 158, 82, 820]], ["chart.js", "0qv3qaj"], ["ext.cx.campaigns.newarticle", "1juulkd", [792, 218, 130]], ["ext.cx.campaigns.newarticle.veloader", "1q3hanv"], ["ext.cx.betafeature.init", "11f121i"], ["ext.cx.campaigns.contributionsmenu", "1gkuwcj", [792, 127, 158, 218]], ["ext.cx.tools.validator", "0nqa1yg", [746]], ["ext.cx.widgets.overlay", "0verknp", [740]], ["ext.cx.widgets.spinner", "0bh20tf", [740]], ["ext.cx.widgets.callout", "0kpx6w0"], ["ext.cx.widgets.translator", "1n804bj", [740, 88, 155]], ["mw.cx.dm", "0k2o3pr", [740, 248]], ["mw.cx.dm.Translation", "1fnnxk9", [794]], ["mw.cx.dm.WikiPage", "1orhwvw", [735, 794]], ["mw.cx.dm.TranslationIssue", "1xupuc7", [794]], [ "mw.cx.dm.PageTitleModel", "12viqy0", [808]], ["mw.cx.ui", "03yyj6l", [740, 251]], ["mw.cx.visualEditor", "07j6l8d", [805, 804, 803, 802, 806, 801]], ["mw.cx.visualEditor.sentence", "0068bap", [809]], ["mw.cx.visualEditor.publishSettings", "0qi27ra", [497]], ["mw.cx.visualEditor.mt", "08yf3sk", [809]], ["mw.cx.visualEditor.link", "0fhi1jr", [809]], ["mw.cx.visualEditor.content", "0deypbd", [809]], ["mw.cx.visualEditor.section", "1tgdlgc", [809, 807, 808]], ["ve.ce.CXLintableNode", "1gdbpxo", [497]], ["ve.dm.CXLintableNode", "19xf3vo", [497, 797]], ["mw.cx.visualEditor.base", "0nlm2n4", [500, 484, 505]], ["mw.cx.init", "0vndcgx", [813, 812, 796, 811]], ["mw.cx.init.Translation", "0dyfymf", [246, 830, 815, 814]], ["mw.cx.MwApiRequestManager", "1r08i3r", [814]], ["mw.cx.MachineTranslation", "0a1pykl", [740, 117]], ["ve.init.mw.CXTarget", "11ztbr7", [746, 743, 795, 833, 744, 817, 816]], ["mw.cx.ui.TranslationView", "16drvb2", [746, 791, 798, 835, 820, 823, 842]], ["ve.ui.CXSurface", "1s2031f", [500]], ["ve.ui.CXDesktopContext", "1z13y80", [500]], [ "mw.cx.ui.TranslationView.legacy", "0wlf66r", [746, 743, 824, 821, 843]], ["mw.cx.init.legacy", "1i6ygtw", [110, 818]], ["mw.cx.ui.Header", "1xv7qds", [846, 274, 275]], ["mw.cx.ui.Header.legacy", "0xy73ca", [823, 846, 274, 275]], ["mw.cx.ui.Header.skin", "1w61nm3"], ["mw.cx.ui.Infobar", "0itr3ze", [844, 744]], ["mw.cx.ui.Columns.legacy", "0g80ejc", [825, 827, 826]], ["mw.cx.ui.SourceColumn.legacy", "0gwx3j3", [791, 799]], ["mw.cx.ui.TranslationColumn.legacy", "1cl553w", [791, 799]], ["mw.cx.ui.ToolsColumn.legacy", "1x8rgqe", [799]], ["mw.cx.ui.CategoryMultiselectWidget", "10450o4", [510, 799]], ["mw.cx.ui.TranslationIssueWidget", "1s32duj", [799]], ["mw.cx.ui.Categories", "10mmjb2", [795, 828]], ["mw.cx.ui.CaptchaDialog", "1x01v33", [1323, 799]], ["mw.cx.ui.LoginDialog", "199mc0s", [130, 799]], ["mw.cx.tools.TranslationToolFactory", "1vnvylp", [799]], ["mw.cx.tools", "07j6l8d", [838, 837, 836]], ["mw.cx.tools.IssueTrackingTool", "1lzsat4", [839, 829]], ["mw.cx.tools.TemplateTool", "0zxxleu", [839]], ["mw.cx.tools.SearchTool", "14ysr8j", [839]], [ "mw.cx.tools.InstructionsTool", "1ssped2", [158, 839]], ["mw.cx.tools.TranslationTool", "01aigt3", [840]], ["mw.cx.ui.TranslationToolWidget", "1o6juy7", [799]], ["mw.cx.widgets.TemplateParamOptionWidget", "1vi1sv5", [799]], ["mw.cx.ui.PageTitleWidget", "0h12rji", [799, 744, 807]], ["mw.cx.ui.PublishSettingsWidget", "0lhxuhz", [799]], ["mw.cx.ui.MessageWidget", "0p4fh0t", [799]], ["mw.cx.ui.PageSelectorWidget", "00rputp", [746, 735, 847]], ["mw.cx.ui.PersonalMenuWidget", "0u7wy82", [128, 222, 799]], ["mw.cx.ui.TitleOptionWidget", "0rv5dqe", [222, 799]], ["mw.externalguidance.init", "0kgags6", [127]], ["mw.externalguidance", "17gthqt", [96, 563, 850, 265]], ["mw.externalguidance.icons", "0o06tln"], ["mw.externalguidance.special", "1v2nu3p", [735, 96, 216, 563, 850]], ["ext.wikimediaBadges", "095wjwn"], ["ext.TemplateSandbox.top", "0lx83kp"], ["ext.TemplateSandbox", "0knytl8", [853]], ["ext.pageassessments.special", "1on8ae2", [41, 252]], ["ext.jsonConfig", "0v8xwbw"], ["ext.graph.styles", "1civrba"], ["ext.graph.data", "1cj2mqm"], ["ext.graph.loader",
    "0nwfgvy", [88]], ["ext.graph.vega1", "1jldn5h", [858, 127]], ["ext.graph.vega2", "0le004j", [858, 127]], ["ext.graph.sandbox", "0t5cocn", [651, 861, 90]], ["ext.graph.visualEditor", "1v744k1", [858, 507]], ["ext.MWOAuth.BasicStyles", "17md9qs"], ["ext.MWOAuth.AuthorizeForm", "0ed1zwc"], ["ext.MWOAuth.AuthorizeDialog", "037oqgi", [54]], ["ext.oath.showqrcode", "19dskm8"], ["ext.oath.showqrcode.styles", "05wl2k0"], ["ext.ores.highlighter", "1bax9ym"], ["ext.ores.styles", "0ml3tq9"], ["ext.ores.specialoresmodels.styles", "1r90z9w"], ["ext.ores.api", "1v5iq8r"], ["ext.checkUser", "0yaon1r", [130]], ["ext.checkUser.caMultiLock", "0nogkx1", [130]], ["ext.quicksurveys.views", "1gxn8qi", [876, 127, 254]], ["ext.quicksurveys.lib", "0e7qtkx", [134, 135, 128, 131]], ["ext.quicksurveys.init", "15lvfar", [876]], ["ext.kartographer", "1dc4652"], ["ext.kartographer.extlinks", "04pzke3"], ["ext.kartographer.style", "0z4ra3x"], ["ext.kartographer.site", "1jxypm8"], ["mapbox", "0n325qh"], ["leaflet.draw", "1e1b7ed", [882]], ["ext.kartographer.link", "1qlg73x", [886, 249]], [ "ext.kartographer.box", "0ua68k6", [887, 899, 890, 881, 880, 891, 46, 127, 88, 271]], ["ext.kartographer.linkbox", "0634jgx", [891]], ["ext.kartographer.data", "1n21d4p"], ["ext.kartographer.dialog", "12w82q5", [249, 257]], ["ext.kartographer.dialog.sidebar", "0cncafl", [879, 268, 273]], ["ext.kartographer.settings", "1tf29um", [878, 882]], ["ext.kartographer.util", "1e3wcps", [878]], ["ext.kartographer.frame", "0vd5gqk", [885, 249]], ["ext.kartographer.staticframe", "02sgs74", [886, 249, 271]], ["ext.kartographer.preview", "0e27ldv"], ["ext.kartographer.editing", "1hmhsxf", [88]], ["ext.kartographer.editor", "07j6l8d", [885, 883]], ["ext.kartographer.visualEditor", "12j5enq", [891, 503, 46, 270]], ["ext.kartographer.lib.prunecluster", "0tqfpwn", [882]], ["ext.kartographer.lib.topojson", "1j7k7hu", [882]], ["ext.kartographer.wv", "16ai8s1", [898, 265]], ["ext.kartographer.specialMap", "028zaua"], ["ext.pageviewinfo", "0mfe00a", [861, 251]], ["three.js", "1robr4b"], ["ext.3d", "0fy9elb", [39]], ["ext.3d.styles", "1jeu1fq"], ["mmv.3d", "1f7v0ta", [904, 458, 903]], [ "mmv.3d.head", "0gme08r", [904, 252, 260]], ["ext.3d.special.upload", "1x9lrec", [909, 201]], ["ext.3d.special.upload.styles", "11hw0fx"], ["ext.GlobalPreferences.global", "0i4bfc9", [222, 230, 238]], ["ext.GlobalPreferences.global-nojs", "0ymd5ai"], ["ext.GlobalPreferences.local", "1dcqsv8", [230]], ["ext.GlobalPreferences.local-nojs", "0d5p1lr"], ["ext.flaggedRevs.basic", "0pbde7g"], ["ext.flaggedRevs.advanced", "0pqqatv", [19]], ["ext.flaggedRevs.review", "17odk2h", [158, 110, 128]], ["ext.flaggedRevs.review.styles", "0z19uxf"], ["ext.cirrus.serp", "0tyysx4", [127]], ["ext.cirrus.explore-similar", "0gzcp5z", [88, 85]], ["ext.collection", "0wdghmg", [922, 64, 155]], ["ext.collection.bookcreator.styles", "1kp652y"], ["ext.collection.bookcreator", "1w73rax", [921, 40]], ["ext.collection.checkLoadFromLocalStorage", "0676033", [920]], ["ext.collection.suggest", "0ziket6", [922]], ["ext.collection.offline", "137oe3t"], ["ext.collection.bookcreator.messageBox", "07j6l8d", [928, 927, 99]], ["ext.collection.bookcreator.messageBox.styles", "1xw4dv5"], [ "ext.collection.bookcreator.messageBox.icons", "157xxtp"], ["mw.config.values.wbSiteDetails", "15epbnr"], ["mw.config.values.wbRepo", "1vusrwy"], ["wikibase", "0z5mytf"], ["wikibase.buildErrorOutput", "0q33lzh", [931]], ["wikibase.sites", "08c9mxn", [929, 1124]], ["wikibase.RepoApi", "1aaoyv7", [931, 1031]], ["wikibase.RepoApiError", "0o2mh6m", [931, 1032]], ["jquery.wikibase.siteselector", "1jwlgv4", [1039, 1044, 1053]], ["jquery.wikibase.wbtooltip", "1w4za80", [47, 67, 932]], ["wikibase.datamodel", "07j6l8d", [943, 947, 952, 953, 954, 955]], ["wikibase.datamodel.__namespace", "0cvu8cr", [931]], ["wikibase.datamodel.Claim", "0znayuw", [961]], ["wikibase.datamodel.Entity", "1cdsnav", [970, 939]], ["wikibase.datamodel.FingerprintableEntity", "1of6k11", [941]], ["wikibase.datamodel.EntityId", "0ts9q35", [972, 939]], ["wikibase.datamodel.Fingerprint", "0pb35ok", [951, 967]], ["wikibase.datamodel.Group", "0odbhph", [939]], ["wikibase.datamodel.GroupableCollection", "07ks5eq", [970, 939]], ["wikibase.datamodel.Item", "05r8rd9", [944, 942, 959, 964]], [ "wikibase.datamodel.List", "15dq64o", [946]], ["wikibase.datamodel.Map", "0fxneke", [939]], ["wikibase.datamodel.MultiTerm", "0urkj1v", [939]], ["wikibase.datamodel.MultiTermMap", "0wpswuc", [970, 949, 950]], ["wikibase.datamodel.Property", "1k5pls5", [944, 942, 964]], ["wikibase.datamodel.PropertyNoValueSnak", "0c7fuxz", [970, 960]], ["wikibase.datamodel.PropertySomeValueSnak", "0i6o7se", [970, 960]], ["wikibase.datamodel.PropertyValueSnak", "0v9cilk", [972, 960]], ["wikibase.datamodel.Reference", "1ua0183", [961]], ["wikibase.datamodel.ReferenceList", "08w3tzn", [956]], ["wikibase.datamodel.SiteLink", "19gn8h9", [939]], ["wikibase.datamodel.SiteLinkSet", "1r2t8r2", [968, 958]], ["wikibase.datamodel.Snak", "0p6e90i", [939]], ["wikibase.datamodel.SnakList", "1lps7lh", [948, 960]], ["wikibase.datamodel.Statement", "1vgv9ed", [940, 957]], ["wikibase.datamodel.StatementGroup", "0w4pmu2", [945, 965]], ["wikibase.datamodel.StatementGroupSet", "1wdvmtu", [968, 963]], ["wikibase.datamodel.StatementList", "1izpnz2", [962]], ["wikibase.datamodel.Term",
    "1nvuh5b", [939]], ["wikibase.datamodel.TermMap", "13fqn2d", [970, 949, 966]], ["wikibase.datamodel.Set", "0q2zahe", [946]], ["globeCoordinate.js", "1lya63y"], ["util.inherit", "1rawg4h"], ["dataValues", "0aykn96"], ["dataValues.DataValue", "0hf8qzu", [971, 970]], ["dataValues.values", "1xp0rsm", [974, 969]], ["dataValues.TimeValue", "10yqwop", [972]], ["valueFormatters", "02bw0u5"], ["valueFormatters.ValueFormatter", "0buzsab", [970, 975]], ["valueFormatters.formatters", "1gwrdjl", [973, 976]], ["valueParsers", "0zd1lpq"], ["valueParsers.ValueParser", "114qfrb", [970, 978]], ["valueParsers.ValueParserStore", "0kgiecz", [978]], ["valueParsers.parsers", "0ru708l", [973, 979]], ["wikibase.serialization", "07j6l8d", [984, 985]], ["wikibase.serialization.__namespace", "06q4u2x", [931]], ["wikibase.serialization.DeserializerFactory", "0j0z39k", [989]], ["wikibase.serialization.SerializerFactory", "1md7m8n", [1008]], ["wikibase.serialization.StrategyProvider", "107qmpf", [983]], ["wikibase.serialization.ClaimDeserializer", "1ws44lm", [940, 999]], [ "wikibase.serialization.Deserializer", "1pu2axt", [970, 983]], ["wikibase.serialization.EntityDeserializer", "1xeiwvn", [991, 994, 986]], ["wikibase.serialization.FingerprintDeserializer", "1k52ru7", [944, 993, 1006]], ["wikibase.serialization.ItemDeserializer", "0ov0tyn", [947, 990, 997, 1001]], ["wikibase.serialization.MultiTermDeserializer", "037ka3n", [950, 988]], ["wikibase.serialization.MultiTermMapDeserializer", "14ri3zv", [951, 992]], ["wikibase.serialization.PropertyDeserializer", "1etw6cw", [952, 990, 1001]], ["wikibase.serialization.ReferenceListDeserializer", "0kfvjwv", [957, 996]], ["wikibase.serialization.ReferenceDeserializer", "1xz2hzv", [956, 999]], ["wikibase.serialization.SiteLinkSetDeserializer", "1pbyaug", [959, 998]], ["wikibase.serialization.SiteLinkDeserializer", "0zieitl", [958, 988]], ["wikibase.serialization.SnakListDeserializer", "1wweikf", [961, 1000]], ["wikibase.serialization.SnakDeserializer", "098c6sk", [973, 953, 954, 955, 988]], ["wikibase.serialization.StatementGroupSetDeserializer", "1y1bc1j", [964, 1002]], [ "wikibase.serialization.StatementGroupDeserializer", "0js33rg", [963, 1003]], ["wikibase.serialization.StatementListDeserializer", "1yrguru", [965, 1004]], ["wikibase.serialization.StatementDeserializer", "1l7u9yb", [962, 987, 995]], ["wikibase.serialization.TermDeserializer", "17xelz0", [966, 988]], ["wikibase.serialization.TermMapDeserializer", "1grgm3h", [967, 1005]], ["wikibase.serialization.ClaimSerializer", "0i53bho", [940, 1019]], ["wikibase.serialization.EntitySerializer", "0u5sveu", [952, 1010, 1013, 986]], ["wikibase.serialization.FingerprintSerializer", "16g7ywk", [944, 1011, 1025]], ["wikibase.serialization.ItemSerializer", "0fz6yu2", [947, 1009, 1018, 1022]], ["wikibase.serialization.MultiTermMapSerializer", "08u4yw5", [951, 1012]], ["wikibase.serialization.MultiTermSerializer", "1k1l503", [950, 1016]], ["wikibase.serialization.PropertySerializer", "1jd8kqj", [947, 1009, 1022]], ["wikibase.serialization.ReferenceListSerializer", "1618kqh", [957, 1015]], ["wikibase.serialization.ReferenceSerializer", "0b3t0bh", [956, 1019]], [ "wikibase.serialization.Serializer", "0q2ekep", [970, 983]], ["wikibase.serialization.SiteLinkSerializer", "1yyr23a", [958, 1016]], ["wikibase.serialization.SiteLinkSetSerializer", "12s4lzy", [959, 1017]], ["wikibase.serialization.SnakListSerializer", "1cpgssg", [961, 1020]], ["wikibase.serialization.SnakSerializer", "03jwb9w", [955, 1016]], ["wikibase.serialization.StatementGroupSerializer", "1rqfqx8", [963, 1023]], ["wikibase.serialization.StatementGroupSetSerializer", "1vy7eoe", [964, 1021]], ["wikibase.serialization.StatementListSerializer", "0uepyo2", [965, 1024]], ["wikibase.serialization.StatementSerializer", "1ehlhc5", [962, 1007, 1014]], ["wikibase.serialization.TermMapSerializer", "05lo7uc", [967, 1026]], ["wikibase.serialization.TermSerializer", "097ufe4", [966, 1016]], ["wikibase.api.__namespace", "1sf6dlj"], ["wikibase.api.FormatValueCaller", "0c5rxch", [972, 1032]], ["wikibase.api.getLocationAgnosticMwApi", "1kc0vtc", [96, 1027]], ["wikibase.api.ParseValueCaller", "1o06t0d", [1032]], ["wikibase.api.RepoApi", "1wx6wi9", [1027]], [ "wikibase.api.RepoApiError", "17ebgk9", [970, 1027]], ["jquery.animateWithEvent", "1ns9ijk", [1034]], ["jquery.AnimationEvent", "1mh6s4r", [1038]], ["jquery.autocompletestring", "1xuk6j9", [1049]], ["jquery.focusAt", "0iwurun"], ["jquery.inputautoexpand", "10mbgbo", [1039]], ["jquery.PurposedCallbacks", "0o3oz96"], ["jquery.event.special.eachchange", "1fudhg9", [24]], ["jquery.ui.inputextender", "0yisl79", [1033, 1039, 59, 67]], ["jquery.ui.listrotator", "1j5gdxh", [51]], ["jquery.ui.ooMenu", "0n42w5m", [67, 1050, 970]], ["jquery.ui.preview", "0j3oh44", [67, 1056, 1055]], ["jquery.ui.suggester", "0716265", [48, 1042, 59]], ["jquery.ui.commonssuggester", "0e00kas", [1044, 1053]], ["jquery.ui.languagesuggester", "1yrv529", [1044]], ["jquery.ui.toggler", "0qvc3sc", [1033, 48, 67]], ["jquery.ui.unitsuggester", "0ry9dq4", [1044]], ["jquery.util.adaptlettercase", "0m6vgh0"], ["jquery.util.getscrollbarwidth", "0igg1wx"], ["util.ContentLanguages", "0yy013j", [970]], ["util.Extendable", "1dwo4ki"], ["util.highlightSubstring", "08zsfcu"], ["util.MessageProvider",
    "0f14o60"], ["util.HashMessageProvider", "02teti0"], ["util.CombiningMessageProvider", "1cvmnfc"], ["util.PrefixingMessageProvider", "18hy7w8"], ["util.Notifier", "0ri6d2e"], ["jquery.valueview", "0ctqo24", [1063]], ["jquery.valueview.Expert", "06mac0y", [1056, 1052, 1055, 1058, 970]], ["jquery.valueview.ExpertStore", "0smnh1x"], ["jquery.valueview.experts", "1q9bwga"], ["jquery.valueview.valueview", "0dlztmv", [972, 67, 1061, 1064, 1068, 1076, 976, 980]], ["jquery.valueview.ViewState", "14qwcii"], ["jquery.valueview.experts.CommonsMediaType", "1s6zmsg", [1045, 1072]], ["jquery.valueview.experts.GeoShape", "1r7iind", [1045, 1072]], ["jquery.valueview.experts.TabularData", "03dqvt9", [1045, 1072]], ["jquery.valueview.experts.EmptyValue", "1uwbkfe", [1060, 1062]], ["jquery.valueview.experts.GlobeCoordinateInput", "0xvzsbr", [1078, 1080, 1081, 1072, 1054]], ["jquery.valueview.experts.MonolingualText", "1jt002a", [1079, 1072]], ["jquery.valueview.experts.QuantityInput", "07ey6te", [1082, 1072]], ["jquery.valueview.experts.StringValue", "189ip5g", [1036,
    1037, 1060, 1062]], ["jquery.valueview.experts.SuggestedStringValue", "1unbz8k", [1044, 1072]], ["jquery.valueview.experts.TimeInput", "1ddzdop", [974, 1078, 1080, 1081, 1054]], ["jquery.valueview.experts.UnDeserializableValue", "09n33bs", [1060, 1062]], ["jquery.valueview.experts.UnsupportedValue", "18mz2zl", [1060, 1062]], ["jquery.valueview.ExpertExtender", "11k7hlr", [1040, 1059]], ["jquery.valueview.ExpertExtender.Container", "1q3f22f", [1077]], ["jquery.valueview.ExpertExtender.LanguageSelector", "1vs5m2y", [1046, 1077, 1057]], ["jquery.valueview.ExpertExtender.Listrotator", "1yrhxjc", [1041, 1077]], ["jquery.valueview.ExpertExtender.Preview", "0tqove0", [1043, 1077, 1057]], ["jquery.valueview.ExpertExtender.UnitSelector", "1ogapzr", [1048, 1077]], ["jquery.ui.closeable", "0hbi899", [1085]], ["jquery.ui.EditableTemplatedWidget", "0i9jkpa", [1083]], ["jquery.ui.TemplatedWidget", "1isirlp", [67, 970, 1098]], ["jquery.wikibase.entityselector", "0umcykj", [1039, 46, 1044]], ["jquery.wikibase.entityview", "0v84w65", [1085]], [ "jquery.wikibase.listview", "0wxte1s", [1085]], ["jquery.wikibase.referenceview", "169v56w", [1084, 1088, 938]], ["jquery.wikibase.statementview", "0f4b3pq", [57, 1047, 1086, 1089, 1091, 212, 930, 1000, 1020, 1104]], ["jquery.wikibase.statementview.RankSelector.styles", "0bu9la5"], ["jquery.wikibase.toolbar.styles", "103stu8"], ["jquery.wikibase.toolbarbutton", "0u9zuvq", [1094, 1095]], ["jquery.wikibase.toolbarbutton.styles", "1goo212"], ["jquery.wikibase.toolbaritem", "0wqqnh1", [1085]], ["wikibase.common", "1qa9cgc"], ["wikibase.RevisionStore", "0kqepox", [931]], ["wikibase.templates", "0lk77lc", [31]], ["wikibase.ValueFormatterFactory", "0t6wu3i", [970, 931]], ["wikibase.entityChangers.EntityChangersFactory", "1fkt2r4", [1032, 950, 1004, 1024]], ["wikibase.entityIdFormatter", "0q0sjht", [970, 1105]], ["wikibase.store.EntityStore", "1ltpd9l", [970, 931]], ["wikibase.utilities.ClaimGuidGenerator", "1aazh85", [970, 1104]], ["wikibase.utilities", "1dwi6zv", [158, 931]], ["wikibase.view.__namespace", "0ibm72h", [931]], ["wikibase.view.ViewController",
    "0tev9qp", [970, 1105]], ["wikibase.view.StructureEditorFactory", "1r80un3", [1105]], ["wikibase.view.ToolbarFactory", "06x0frf", [1092, 1093, 937, 1032, 1105]], ["wikibase.view.ControllerViewFactory", "0yy81h4", [1106, 1112]], ["wikibase.view.ReadModeViewFactory", "0rscmvf", [1112]], ["wikibase.view.ViewFactoryFactory", "0wgzplb", [1109, 1110]], ["wikibase.view.ViewFactory", "06dyory", [1037, 1125, 1059, 1087, 936, 1090, 251, 932, 1126, 933, 1103, 1105]], ["wikibase.client.getMwApiForRepo", "1atj1v9", [930, 1029]], ["wikibase.client.init", "1k0gozq"], ["wikibase.client.currentSite", "1owfkh9"], ["wikibase.client.page-move", "1hxkx0q"], ["wikibase.client.changeslist.css", "0tuo5p8"], ["wikibase.client.linkitem.init", "10w8xfo", [39, 110]], ["wikibase.client.PageConnector", "1e9j99u", [933]], ["jquery.wikibase.linkitem", "1it02rp", [39, 54, 936, 937, 158, 1031, 1032, 1119, 1115]], ["wikibase.client.action.edit.collapsibleFooter", "07en7qb", [36, 105, 117]], ["ext.centralauth.globalrenamequeue", "0t1osl6"], ["ext.centralauth.globalrenamequeue.styles", "1lbp82s"], ["wikibase.Site", "117oxf7", [726, 970, 931]], ["jquery.util.getDirectionality", "12tovhk", [726]], ["wikibase.getLanguageNameByCode", "0gg1y8g", [726, 931]], ["skins.monobook.mobile.echohack", "1dqtq52", [643, 130]], ["skins.monobook.mobile.uls", "1t3lsx8", [722]], ["ext.cite.visualEditor.core", "077yjgj", [503]], ["ext.cite.visualEditor.data", "1w4rwbr", [493]], ["ext.cite.visualEditor", "1yd09or", [297, 294, 1129, 1130, 511, 264]], ["ext.geshi.visualEditor", "0av2r56", [503]], ["ext.gadget.modrollback", "0m8kr75", [], "site"], ["ext.gadget.confirmationRollback-mobile", "06t996b", [130], "site"], ["ext.gadget.removeAccessKeys", "1v4dp6y", [5, 19], "site"], ["ext.gadget.searchFocus", "1kcxw0b", [], "site"], ["ext.gadget.GoogleTrans", "0i9xpb5", [], "site"], ["ext.gadget.ImageAnnotator", "1r8tvav", [], "site"], ["ext.gadget.imagelinks", "03oacn1", [130], "site"], ["ext.gadget.Navigation_popups", "0acjdvh", [128], "site"], ["ext.gadget.exlinks", "1yffrxe", [130], "site"], ["ext.gadget.search-new-tab", "125v5mi", [], "site"], ["ext.gadget.PrintOptions", "0yj4fio"
    , [], "site"], ["ext.gadget.revisionjumper", "13grkt7", [], "site"], ["ext.gadget.Twinkle", "1bzw269", [128, 110, 54, 47, 82], "site"], ["ext.gadget.Twinkle-pagestyles", "1n864ve", [], "site"], ["ext.gadget.HideFundraisingNotice", "1nofexb", [], "site"], ["ext.gadget.HideCentralNotice", "0gz251v", [], "site"], ["ext.gadget.teahouse", "0ogf1oh", [], "site"], ["ext.gadget.ReferenceTooltips", "19fngpk", [134, 24, 110], "site"], ["ext.gadget.formWizard", "17lv5kf", [], "site"], ["ext.gadget.formWizard-core", "0gw0ojn", [134, 214, 128, 23, 54], "site"], ["ext.gadget.responsiveContentBase", "1pafwwc", [], "site"], ["ext.gadget.responsiveContentBaseTimeless", "0dgln69", [], "site"], ["ext.gadget.geonotice", "0qnui3g", [], "site"], ["ext.gadget.geonotice-core", "07wxam3", [130, 117], "site"], ["ext.gadget.watchlist-notice", "0fams1t", [], "site"], ["ext.gadget.watchlist-notice-core", "1vpku5x", [117], "site"], ["ext.gadget.WatchlistBase", "00nm77a", [], "site"], ["ext.gadget.WatchlistGreenIndicators", "00rse6b", [], "site"], ["ext.gadget.WatchlistGreenIndicatorsMono",
    "1vcwo5n", [], "site"], ["ext.gadget.WatchlistChangesBold", "0x4gb0g", [], "site"], ["ext.gadget.SubtleUpdatemarker", "0szsh3x", [], "site"], ["ext.gadget.defaultsummaries", "038hmdv", [252], "site"], ["ext.gadget.citations", "15h3a4i", [130], "site"], ["ext.gadget.DotsSyntaxHighlighter", "1h0h45z", [], "site"], ["ext.gadget.HotCat", "0q3ao28", [], "site"], ["ext.gadget.wikEdDiff", "0zpf2j3", [], "site"], ["ext.gadget.ProveIt", "0otuf40", [], "site"], ["ext.gadget.ProveIt-classic", "0gxb160", [65, 52, 76, 45, 130], "site"], ["ext.gadget.Shortdesc-helper", "1ksfuic", [88, 110], "site"], ["ext.gadget.Shortdesc-helper-pagestyles-vector", "0ptc7rw", [], "site"], ["ext.gadget.wikEd", "1ehfq60", [45, 8], "site"], ["ext.gadget.afchelper", "1tbzl5j", [128, 23], "site"], ["ext.gadget.DRN-wizard", "0cg7p8r", [], "site"], ["ext.gadget.charinsert", "1vxzqcj", [], "site"], ["ext.gadget.charinsert-core", "1ulfmdi", [45, 5, 117], "site"], ["ext.gadget.charinsert-styles", "0a4ljqw", [], "site"], ["ext.gadget.legacyToolbar", "180rfhd", [], "site"], ["ext.gadget.refToolbar", "1hiau2u", [ 8, 130], "site"], ["ext.gadget.refToolbarBase", "15bvuaq", [], "site"], ["ext.gadget.extra-toolbar-buttons", "1rdtwjl", [], "site"], ["ext.gadget.extra-toolbar-buttons-core", "0cpsse9", [], "site"], ["ext.gadget.edittop", "18hj0jd", [8, 130], "site"], ["ext.gadget.UTCLiveClock", "0lfyjdk", [88, 110], "site"], ["ext.gadget.UTCLiveClock-pagestyles", "1ch76t1", [], "site"], ["ext.gadget.purgetab", "17dzuzd", [88, 110], "site"], ["ext.gadget.ExternalSearch", "0nf3zpq", [], "site"], ["ext.gadget.CollapsibleNav", "15151qt", [28, 42], "site"], ["ext.gadget.MenuTabsToggle", "0xxz2q4", [134], "site"], ["ext.gadget.dropdown-menus", "0bsycfs", [], "site"], ["ext.gadget.dropdown-menus-vector", "1gru6qi", [88, 8], "site"], ["ext.gadget.dropdown-menus-vector-pagestyles", "0lqa2ri", [], "site"], ["ext.gadget.dropdown-menus-nonvector", "1qbsbex", [], "site"], ["ext.gadget.CategoryAboveAll", "0k0g79n", [], "site"], ["ext.gadget.addsection-plus", "11kggo9", [], "site"], ["ext.gadget.CommentsInLocalTime", "02t3l2y", [], "site"], ["ext.gadget.OldDiff", "1x32c83", [], "site"], [ "ext.gadget.NoAnimations", "0y4mrpl", [], "site"], ["ext.gadget.disablesuggestions", "1b1l2e1", [], "site"], ["ext.gadget.NoSmallFonts", "1gtz5x9", [], "site"], ["ext.gadget.topalert", "1a54z4z", [], "site"], ["ext.gadget.metadata", "01siw6r", [130], "site"], ["ext.gadget.JustifyParagraphs", "0ryzphm", [], "site"], ["ext.gadget.righteditlinks", "0z7u80c", [], "site"], ["ext.gadget.PrettyLog", "00fyswo", [130], "site"], ["ext.gadget.switcher", "0g3anh6", [], "site"], ["ext.gadget.SidebarTranslate", "1e1ejya", [], "site"], ["ext.gadget.Blackskin", "180j5cz", [], "site"], ["ext.gadget.VectorClassic", "16fzl1q", [], "site"], ["ext.gadget.widensearch", "1bwkhqx", [], "site"], ["ext.gadget.DisambiguationLinks", "03z85wd", [], "site"], ["ext.gadget.markblocked", "1sfaj8w", [130, 167], "site"], ["ext.gadget.responsiveContent", "1a6aydo", [], "site"], ["ext.gadget.responsiveContentTimeless", "0kihssa", [], "site"], ["ext.gadget.HideInterwikiSearchResults", "03xbpcp", [], "site"], ["ext.gadget.XTools-ArticleInfo", "0pqhi30", [], "site"], ["ext.gadget.RegexMenuFramework",
    "0xtf8zm", [], "site"], ["ext.gadget.ShowMessageNames", "0lxtkzc", [130], "site"], ["ext.gadget.DebugMode", "0pa6gqh", [130], "site"], ["ext.gadget.contribsrange", "0hod987", [130, 39], "site"], ["ext.gadget.BugStatusUpdate", "1dur13k", [], "site"], ["ext.gadget.RTRC", "0di1gsq", [], "site"], ["ext.gadget.mobile-sidebar", "03gfpfe", [], "site"], ["ext.gadget.addMe", "1qilc5d", [], "site"], ["ext.gadget.NewImageThumb", "1qjwy3f", [], "site"], ["ext.gadget.StickyTableHeaders", "147fzm1", [], "site"], ["ext.gadget.ShowJavascriptErrors", "0l317nl", [110], "site"], ["ext.gadget.PageDescriptions", "03s3iv3", [88], "site"], ["ext.gadget.Hide-curationtools", "1qtzcw0", [], "site"], ["ext.gadget.script-installer", "01rz9u6", [], "site"], ["ext.gadget.libLua", "1yyjo7w", [88], "site"], ["ext.gadget.libSensitiveIPs", "1l2zu3u", [1232], "site"], ["ext.globalCssJs.user", "0qvuzjn", [], "user", "metawiki"], ["ext.globalCssJs.user.styles", "0qvuzjn", [], "user", "metawiki"], ["ext.globalCssJs.site", "1r8qw7v", [], "site", "metawiki"], ["ext.globalCssJs.site.styles", "1r8qw7v", [], "site", "metawiki"], ["ext.guidedTour.tour.RcFiltersIntro", "16v26c4", [547]], ["ext.guidedTour.tour.WlFiltersIntro", "1w5lk35", [547]], ["ext.guidedTour.tour.RcFiltersHighlight", "0jzi34j", [547]], ["pdfhandler.messages", "1f9j6mx"], ["ext.visualEditor.mwextensionmessages", "06o1wm3"], ["ext.guidedTour.tour.firsteditve", "1koe47l", [547]], ["mobile.notifications.overlay", "1xdo8iv", [633, 563, 251]], ["ext.pageTriage.views.toolbar", "1em15wl", [627, 625, 621, 620, 618, 39, 52, 55, 158, 107, 616, 620]], ["ext.echo.emailicons", "0p4dcdj"], ["ext.echo.secondaryicons", "0xma6bx"], ["ext.guidedTour.tour.gettingstartedtasktoolbarve", "1jtj5tf", [664, 547]], ["schema.CentralNoticeBannerHistory", "07j6l8d", [705]], ["schema.MediaViewer", "07j6l8d", [705]], ["schema.MultimediaViewerNetworkPerformance", "07j6l8d", [705]], ["schema.MultimediaViewerDuration", "07j6l8d", [705]], ["schema.MultimediaViewerAttribution", "07j6l8d", [705]], ["schema.MultimediaViewerDimensions", "07j6l8d", [705]], ["schema.Popups", "07j6l8d", [705]], ["schema.VirtualPageView",
    "07j6l8d", [705]], ["schema.GuidedTourGuiderImpression", "07j6l8d", [705]], ["schema.GuidedTourGuiderHidden", "07j6l8d", [705]], ["schema.GuidedTourButtonClick", "07j6l8d", [705]], ["schema.GuidedTourInternalLinkActivation", "07j6l8d", [705]], ["schema.GuidedTourExternalLinkActivation", "07j6l8d", [705]], ["schema.GuidedTourExited", "07j6l8d", [705]], ["schema.MobileWebSearch", "07j6l8d", [705]], ["schema.WebClientError", "07j6l8d", [705]], ["schema.MobileWebShareButton", "07j6l8d", [705]], ["schema.MobileWebMainMenuClickTracking", "07j6l8d", [705]], ["schema.GettingStartedRedirectImpression", "07j6l8d", [705]], ["schema.SignupExpCTAButtonClick", "07j6l8d", [705]], ["schema.SignupExpCTAImpression", "07j6l8d", [705]], ["schema.SignupExpPageLinkClick", "07j6l8d", [705]], ["schema.TaskRecommendation", "07j6l8d", [705]], ["schema.TaskRecommendationClick", "07j6l8d", [705]], ["schema.TaskRecommendationImpression", "07j6l8d", [705]], ["schema.TaskRecommendationLightbulbClick", "07j6l8d", [705]], ["schema.TwoColConflictConflict", "07j6l8d", [705]], [ "schema.Print", "07j6l8d", [705]], ["schema.ReadingDepth", "07j6l8d", [705]], ["schema.EditAttemptStep", "07j6l8d", [705]], ["schema.VisualEditorFeatureUse", "07j6l8d", [705]], ["schema.CompletionSuggestions", "07j6l8d", [705]], ["schema.SearchSatisfaction", "07j6l8d", [705]], ["schema.TestSearchSatisfaction2", "07j6l8d", [705]], ["schema.SearchSatisfactionErrors", "07j6l8d", [705]], ["schema.Search", "07j6l8d", [705]], ["schema.ChangesListHighlights", "07j6l8d", [705]], ["schema.ChangesListFilterGrouping", "07j6l8d", [705]], ["schema.RecentChangesTopLinks", "07j6l8d", [705]], ["schema.InputDeviceDynamics", "07j6l8d", [705]], ["schema.CitationUsage", "07j6l8d", [705]], ["schema.CitationUsagePageLoad", "07j6l8d", [705]], ["schema.WMDEBannerEvents", "07j6l8d", [705]], ["schema.WMDEBannerSizeIssue", "07j6l8d", [705]], ["schema.WikidataCompletionSearchClicks", "07j6l8d", [705]], ["schema.UserFeedback", "07j6l8d", [705]], ["schema.UniversalLanguageSelector", "07j6l8d", [705]], ["schema.ContentTranslation", "07j6l8d", [705]], [ "schema.ContentTranslationCTA", "07j6l8d", [705]], ["schema.ContentTranslationAbuseFilter", "07j6l8d", [705]], ["schema.ContentTranslationSuggestion", "07j6l8d", [705]], ["schema.ContentTranslationError", "07j6l8d", [705]], ["schema.QuickSurveysResponses", "07j6l8d", [705]], ["schema.QuickSurveyInitiation", "07j6l8d", [705]], ["schema.AdvancedSearchRequest", "07j6l8d", [705]], ["schema.TemplateWizard", "07j6l8d", [705]], ["schema.ArticleCreationWorkflow", "07j6l8d", [705]], ["schema.EchoInteraction", "07j6l8d", [705]], ["schema.NavigationTiming", "07j6l8d", [705]], ["schema.SaveTiming", "07j6l8d", [705]], ["schema.ResourceTiming", "07j6l8d", [705]], ["schema.CentralNoticeTiming", "07j6l8d", [705]], ["schema.CpuBenchmark", "07j6l8d", [705]], ["schema.ServerTiming", "07j6l8d", [705]], ["schema.RUMSpeedIndex", "07j6l8d", [705]], ["schema.PaintTiming", "07j6l8d", [705]], ["schema.ElementTiming", "07j6l8d", [705]], ["schema.LayoutJank", "07j6l8d", [705]], ["schema.EventTiming", "07j6l8d", [705]], ["schema.ClickTiming", "07j6l8d", [705]], [ "schema.ExternalGuidance", "07j6l8d", [705]], ["ext.wikimediaEvents.visualEditor", "18dtl8c", [483]], ["ext.uls.displaysettings", "0vov24a", [725, 726, 730, 215]], ["ext.uls.preferences", "13scwrq", [128]], ["mw.cx.externalmessages", "0rlq2j1"], ["ext.quicksurveys.survey.perceived-performance-survey", "17ynvi1"]]);

    mw.config.set( {
        "wgLoadScript":"/w/load.php", "debug": !1, "skin":"vector", "stylepath":"/w/skins", "wgUrlProtocols":"bitcoin\\:|ftp\\:\\/\\/|ftps\\:\\/\\/|geo\\:|git\\:\\/\\/|gopher\\:\\/\\/|http\\:\\/\\/|https\\:\\/\\/|irc\\:\\/\\/|ircs\\:\\/\\/|magnet\\:|mailto\\:|mms\\:\\/\\/|news\\:|nntp\\:\\/\\/|redis\\:\\/\\/|sftp\\:\\/\\/|sip\\:|sips\\:|sms\\:|ssh\\:\\/\\/|svn\\:\\/\\/|tel\\:|telnet\\:\\/\\/|urn\\:|worldwind\\:\\/\\/|xmpp\\:|\\/\\/", "wgArticlePath":"/wiki/$1", "wgScriptPath":"/w", "wgScript":"/w/index.php", "wgSearchType":"CirrusSearch", "wgVariantArticlePath": !1, "wgActionPaths": {}

        , "wgServer":"//en.wikipedia.org", "wgServerName":"en.wikipedia.org", "wgUserLanguage":"en", "wgContentLanguage": "en", "wgTranslateNumerals": !0, "wgVersion":"1.34.0-wmf.4", "wgEnableAPI": !0, "wgEnableWriteAPI": !0, "wgFormattedNamespaces": {
            "-2": "Media", "-1":"Special", "0":"", "1":"Talk", "2":"User", "3":"User talk", "4":"Wikipedia", "5":"Wikipedia talk", "6":"File", "7":"File talk", "8":"MediaWiki", "9":"MediaWiki talk", "10":"Template", "11":"Template talk", "12":"Help", "13":"Help talk", "14":"Category", "15":"Category talk", "100":"Portal", "101":"Portal talk", "108":"Book", "109":"Book talk", "118":"Draft", "119":"Draft talk", "446":"Education Program", "447":"Education Program talk", "710":"TimedText", "711":"TimedText talk", "828":"Module", "829":"Module talk", "2300":"Gadget", "2301":"Gadget talk", "2302":"Gadget definition", "2303":"Gadget definition talk"
        }

        , "wgNamespaceIds": {
            "media": -2, "special":-1, "":0, "talk":1, "user":2, "user_talk":3, "wikipedia":4, "wikipedia_talk":5, "file":6, "file_talk":7, "mediawiki":8, "mediawiki_talk":9, "template":10, "template_talk":11, "help":12, "help_talk":13, "category":14, "category_talk":15, "portal"
                :100, "portal_talk":101, "book":108, "book_talk":109, "draft":118, "draft_talk":119, "education_program":446, "education_program_talk":447, "timedtext":710, "timedtext_talk":711, "module":828, "module_talk":829, "gadget":2300, "gadget_talk":2301, "gadget_definition":2302, "gadget_definition_talk":2303, "wp":4, "wt":5, "image":6, "image_talk":7, "project":4, "project_talk":5
        }

        , "wgContentNamespaces":[0], "wgSiteName":"Wikipedia", "wgDBname":"enwiki", "wgExtraSignatureNamespaces":[4, 12], "wgExtensionAssetsPath":"/w/extensions", "wgCookiePrefix":"enwiki", "wgCookieDomain":"", "wgCookiePath":"/", "wgCookieExpiration":2592000, "wgCaseSensitiveNamespaces":[2302, 2303], "wgLegalTitleChars":" %!\"$\u0026'()*,\\-./0-9:;=?@A-Z\\\\\\^_`a-z~+\\u0080-\\uFFFF", "wgIllegalFileChars":":/\\\\", "wgResourceLoaderStorageVersion":"1-3", "wgResourceLoaderStorageEnabled": !0, "wgForeignUploadTargets":["shared"], "wgEnableUploads": !0, "wgCommentByteLimit":null, "wgCommentCodePointLimit":500, "wgCirrusSearchFeedbackLink": !1,
        "wgCiteVisualEditorOtherGroup": !1, "wgCiteResponsiveReferences": !0, "wgTimedMediaHandler": {
            "MediaWiki.DefaultProvider":"local", "MediaWiki.ApiProviders": {
                "wikimediacommons": {
                    "url": "//commons.wikimedia.org/w/api.php"
                }
            }

            , "EmbedPlayer.OverlayControls": !0, "EmbedPlayer.CodecPreference":["vp9", "webm", "h264", "ogg", "mp3", "ogvjs"], "EmbedPlayer.DisableVideoTagSupport": !1, "EmbedPlayer.DisableHTML5FlashFallback": !0, "EmbedPlayer.ReplaceSources":null, "EmbedPlayer.EnableFlavorSelector": !1, "EmbedPlayer.EnableIpadHTMLControls": !0, "EmbedPlayer.WebKitPlaysInline": !1, "EmbedPlayer.EnableIpadNativeFullscreen": !1, "EmbedPlayer.iPhoneShowHTMLPlayScreen": !0, "EmbedPlayer.ForceLargeReplayButton": !1, "EmbedPlayer.RewriteSelector":"video,audio,playlist", "EmbedPlayer.DefaultSize":"400x300", "EmbedPlayer.ControlsHeight":31, "EmbedPlayer.TimeDisplayWidth":85, "EmbedPlayer.KalturaAttribution": !0, "EmbedPlayer.EnableOptionsMenu": !0, "EmbedPlayer.EnableRightClick": !0,
            "EmbedPlayer.EnabledOptionsMenuItems":["playerSelect", "download", "share", "aboutPlayerLibrary"], "EmbedPlayer.WaitForMeta": !0, "EmbedPlayer.ShowNativeWarning": !0, "EmbedPlayer.ShowPlayerAlerts": !0, "EmbedPlayer.EnableFullscreen": !0, "EmbedPlayer.EnableTimeDisplay": !0, "EmbedPlayer.EnableVolumeControl": !0, "EmbedPlayer.NewWindowFullscreen": !1, "EmbedPlayer.FullscreenTip": !0, "EmbedPlayer.DirectFileLinkWarning": !0, "EmbedPlayer.NativeControls": !1, "EmbedPlayer.NativeControlsMobileSafari": !0, "EmbedPlayer.FullScreenZIndex":999998, "EmbedPlayer.ShareEmbedMode":"iframe", "EmbedPlayer.MonitorRate":250, "EmbedPlayer.UseFlashOnAndroid": !1, "EmbedPlayer.EnableURLTimeEncoding":"flash", "EmbedPLayer.IFramePlayer.DomainWhiteList":"*", "EmbedPlayer.EnableIframeApi": !0, "EmbedPlayer.PageDomainIframe": !0, "EmbedPlayer.NotPlayableDownloadLink": !0, "TimedText.ShowInterface":"always", "TimedText.ShowAddTextLink": !0, "TimedText.ShowRequestTranscript": !1, "TimedText.NeedsTranscriptCategory": "Videos needing subtitles", "TimedText.BottomPadding":10, "TimedText.BelowVideoBlackBoxHeight":40
        }

        , "wgCentralAuthCheckLoggedInURL":"//login.wikimedia.org/wiki/Special:CentralAutoLogin/checkLoggedIn?type=script\u0026wikiid=enwiki", "wgWikiEditorMagicWords": {
            "redirect": "#REDIRECT", "img_right":"right", "img_left":"left", "img_none":"none", "img_center":"center", "img_thumbnail":"thumb", "img_framed":"frame", "img_frameless":"frameless"
        }

        , "mw.msg.wikieditor":"--~~~~", "wgMultimediaViewer": {
            "infoLink":"https://mediawiki.org/wiki/Special:MyLanguage/Extension:Media_Viewer/About", "discussionLink":"https://mediawiki.org/wiki/Special:MyLanguage/Extension_talk:Media_Viewer/About", "helpLink":"https://mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Media_Viewer", "useThumbnailGuessing": !0, "durationSamplingFactor":1000, "durationSamplingFactorLoggedin": !1, "networkPerformanceSamplingFactor":1000, "actionLoggingSamplingFactorMap": {
                "default": 10, "close":4000, "defullscreen":45, "download":40, "download-close": 240, "download-open":320, "embed-select-menu-html-original":30, "enlarge":40, "file-description-page-abovefold":30, "fullscreen":80, "hash-load":400, "history-navigation":1600, "image-view":10000, "metadata-close":160, "metadata-open":70, "metadata-scroll-close":535, "metadata-scroll-open":645, "next-image":2500, "prev-image":1000, "right-click-image":340, "thumbnail":5000, "view-original-file":523
            }

            , "attributionSamplingFactor":1000, "dimensionSamplingFactor":1000, "imageQueryParameter": !1, "recordVirtualViewBeaconURI":"/beacon/media", "tooltipDelay":1000, "extensions": {
                "jpg": "default", "jpeg":"default", "gif":"default", "svg":"default", "png":"default", "tiff":"default", "tif":"default", "stl":"mmv.3d"
            }
        }

        , "wgMediaViewer": !0, "wgMediaViewerIsInBeta": !1, "wgPopupsVirtualPageViews": !0, "wgPopupsGateway":"restbaseHTML", "wgPopupsEventLogging": !1, "wgPopupsRestGatewayEndpoint":"/api/rest_v1/page/summary/", "wgPopupsStatsvSamplingRate":0.01, "wgVisualEditorConfig": {

            "usePageImages": !0, "usePageDescriptions": !0,
            "disableForAnons": !0, "preloadModules":["site", "user"], "preferenceModules": {
                "visualeditor-enable-experimental": "ext.visualEditor.experimental"
            }

            , "namespaces":[100, 108, 118, 2, 6, 12, 14, 0], "contentModels": {
                "wikitext": "article"
            }

            , "pluginModules":["ext.wikihiero.visualEditor", "ext.cite.visualEditor", "ext.geshi.visualEditor", "ext.spamBlacklist.visualEditor", "ext.titleblacklist.visualEditor", "ext.score.visualEditor", "ext.confirmEdit.visualEditor", "ext.CodeMirror.visualEditor.init", "ext.CodeMirror.visualEditor", "ext.templateDataGenerator.editPage", "ext.math.visualEditor", "ext.disambiguator.visualEditor", "ext.wikimediaEvents.visualEditor", "ext.graph.visualEditor", "ext.kartographer.editing", "ext.kartographer.visualEditor", "ext.abuseFilter.visualEditor", "ext.citoid.visualEditor"], "thumbLimits":[120, 150, 180, 200, 220, 250, 300, 400], "galleryOptions": {
                "imagesPerRow": 0, "imageWidth":120, "imageHeight":120, "captionLength": !0, "showBytes": !0, "mode":"traditional", "showDimensions": !0
            }

            , "blacklist": {
                "firefox":
                    [["\u003C=", 11]], "safari":[["\u003C=", 6]], "opera":[["\u003C", 12]]
            }

            , "tabPosition":"before", "tabMessages": {
                "edit": null, "editsource":"visualeditor-ca-editsource", "create":null, "createsource":"visualeditor-ca-createsource", "editlocaldescription":"edit-local", "editlocaldescriptionsource":"visualeditor-ca-editlocaldescriptionsource", "createlocaldescription":"create-local", "createlocaldescriptionsource":"visualeditor-ca-createlocaldescriptionsource", "editsection":"editsection", "editsectionsource":"visualeditor-ca-editsource-section"
            }

            , "singleEditTab": !0, "enableVisualSectionEditing":"mobile-ab", "showBetaWelcome": !0, "allowExternalLinkPaste": !1, "enableTocWidget": !1, "enableWikitext": !0, "svgMaxSize":4096, "namespacesWithSubpages": {
                "6": 0, "8":0, "1": !0, "2": !0, "3": !0, "4": !0, "5": !0, "7": !0, "9": !0, "10": !0, "11": !0, "12": !0, "13": !0, "14": !0, "15": !0, "100": !0, "101": !0, "102": !0, "103": !0, "104": !0, "105": !0, "106": !0, "107": !0, "108": !0, "109": !0, "110": !0, "111"
                    : !0, "112": !0, "113": !0, "114": !0, "115": !0, "116": !0, "117": !0, "118": !0, "119": !0, "447": !0, "830": !0, "831": !0, "828": !0, "829": !0
            }

            , "specialBooksources":"Special:BookSources", "rebaserUrl": !1, "restbaseUrl":"/api/rest_v1/page/html/", "fullRestbaseUrl":"/api/rest_", "feedbackApiUrl": !1, "feedbackTitle": !1, "sourceFeedbackTitle": !1
        }

        , "wgCitoidConfig": {
            "citoidServiceUrl":  !1, "fullRestbaseUrl": !1
        }

        , "wgGuidedTourHelpGuiderUrl":"Help:Guided tours/guider", "wgPageTriageCurationModules": {
            "articleInfo": {
                "helplink": "//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#PageInfo", "namespace":[0, 2]
            }

            , "mark": {
                "helplink": "//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#MarkReviewed", "namespace":[0, 2], "note":[0]
            }

            , "tags": {
                "helplink": "//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#AddTags", "namespace":[0]
            }

            , "delete": {
                "helplink": "//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#MarkDeletion", "namespace":[0, 2]
            }

            , "wikiLove": {
                "helplink":
                    "//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#WikiLove", "namespace":[0, 2]
            }
        }

        , "pageTriageNamespaces":[0, 2, 118], "wgPageTriageDraftNamespaceId":118, "wgTalkPageNoteTemplate": {
            "Mark":"Reviewednote-NPF", "UnMark": {
                "note": "Unreviewednote-NPF", "nonote":"Unreviewednonote-NPF"
            }

            , "Tags":"Taggednote-NPF"
        }

        , "wgEchoMaxNotificationCount":99, "wgGettingStartedConfig": {
            "hasCategories":  !0
        }

        , "wgRelatedArticlesCardLimit":3, "wgEventLoggingBaseUri":"https://en.wikipedia.org/beacon/event", "wgEventLoggingSchemaApiUri":"https://meta.wikimedia.org/w/api.php", "wgEventLoggingSchemaRevision": {
            "CentralNoticeBannerHistory": 14321636, "MediaViewer":10867062, "MultimediaViewerNetworkPerformance":15573630, "MultimediaViewerDuration":10427980, "MultimediaViewerAttribution":9758179, "MultimediaViewerDimensions":10014238, "Popups":17807993, "VirtualPageView":17780078, "GuidedTourGuiderImpression":8694395, "GuidedTourGuiderHidden":8690549, "GuidedTourButtonClick":13869649, "GuidedTourInternalLinkActivation":8690553,
                "GuidedTourExternalLinkActivation":8690560, "GuidedTourExited":8690566, "MobileWebSearch":12054448, "WebClientError":18340282, "MobileWebShareButton":18923688, "MobileWebMainMenuClickTracking":18984528, "GettingStartedRedirectImpression":7355552, "SignupExpCTAButtonClick":8965028, "SignupExpCTAImpression":8965023, "SignupExpPageLinkClick":8965014, "TaskRecommendation":9266319, "TaskRecommendationClick":9266317, "TaskRecommendationImpression":9266226, "TaskRecommendationLightbulbClick":9433256, "TwoColConflictConflict":18155295, "Print":17630514, "ReadingDepth":18201205, "EditAttemptStep":18953228, "VisualEditorFeatureUse":18457512, "CompletionSuggestions":13630018, "SearchSatisfaction":17378115, "TestSearchSatisfaction2":16909631, "SearchSatisfactionErrors":17181648, "Search":14361785, "ChangesListHighlights":16484288, "ChangesListFilterGrouping":17008168, "RecentChangesTopLinks":16732249, "InputDeviceDynamics":17687647, "CitationUsage":18810892, "CitationUsagePageLoad":18502712, "WMDEBannerEvents":18437830,
                "WMDEBannerSizeIssue":18193993, "WikidataCompletionSearchClicks":18665070, "UserFeedback":18903446, "UniversalLanguageSelector":17799034, "ContentTranslation":18999884, "ContentTranslationCTA":16017678, "ContentTranslationAbuseFilter":18472730, "ContentTranslationSuggestion":19004928, "ContentTranslationError":11767097, "QuickSurveysResponses":18397510, "QuickSurveyInitiation":18397507, "AdvancedSearchRequest":18227136, "TemplateWizard":18374327, "ArticleCreationWorkflow":17145434, "EchoInteraction":15823738, "NavigationTiming":18988839, "SaveTiming":15396492, "ResourceTiming":18358918, "CentralNoticeTiming":18418286, "CpuBenchmark":18436118, "ServerTiming":18622171, "RUMSpeedIndex":18813781, "PaintTiming":19000009, "ElementTiming":18951358, "LayoutJank":18935150, "EventTiming":18902447, "ClickTiming":19037039, "ExternalGuidance":18903973
        }

        , "wgWMEStatsdBaseUri":"/beacon/statsv", "wgWMEReadingDepthSamplingRate":0.1, "wgWMEReadingDepthEnabled": !0, "wgWMEPrintSamplingRate":0, "wgWMEPrintEnabled": !0,
        "wgWMECitationUsagePopulationSize":0, "wgWMECitationUsagePageLoadPopulationSize":0, "wgWMESchemaEditAttemptStepSamplingRate":"0.0625", "wgWMEWikidataCompletionSearchClicks":[], "wgWMEPhp7SamplingRate":20, "wgULSIMEEnabled": !1, "wgULSWebfontsEnabled": !1, "wgULSPosition":"interlanguage", "wgULSAnonCanChangeLanguage": !1, "wgULSEventLogging": !0, "wgULSImeSelectors":["input:not([type])", "input[type=text]", "input[type=search]", "textarea", "[contenteditable]"], "wgULSNoImeSelectors":["#wpCaptchaWord", ".ve-ce-surface-paste", ".ve-ce-surface-readOnly [contenteditable]", ".ace_editor textarea"], "wgULSNoWebfontsSelectors":["#p-lang li.interlanguage-link \u003E a"], "wgULSFontRepositoryBasePath":"/w/extensions/UniversalLanguageSelector/data/fontrepo/fonts/", "wgContentTranslationTranslateInTarget": !0, "wgContentTranslationDomainCodeMapping": {
            "be-tarask": "be-x-old", "bho":"bh", "crh-latn":"crh", "gsw":"als", "lzh":"zh-classical", "nan":"zh-min-nan", "nb":"no", "rup":"roa-rup", "sgs":"bat-smg", "vro":"fiu-vro",
                "yue":"zh-yue"
        }

        , "wgContentTranslationSiteTemplates": {
            "view": "//$1.wikipedia.org/wiki/$2", "action":"//$1.wikipedia.org/w/index.php?title=$2", "api":"//$1.wikipedia.org/w/api.php", "cx":"//cxserver.wikimedia.org/v1", "cookieDomain":null, "restbase":"//$1.wikipedia.org/api/rest_v1"
        }

        , "wgContentTranslationTargetNamespace":0, "wgExternalGuidanceMTReferrers":["translate.google.com", "translate.googleusercontent.com"], "wgExternalGuidanceSiteTemplates": {
            "view": "//$1.wikipedia.org/wiki/$2", "action":"//$1.wikipedia.org/w/index.php?title=$2", "api":"//$1.wikipedia.org/w/api.php"
        }

        , "wgExternalGuidanceDomainCodeMapping": {
            "be-tarask": "be-x-old", "bho":"bh", "crh-latn":"crh", "gsw":"als", "lzh":"zh-classical", "nan":"zh-min-nan", "nb":"no", "rup":"roa-rup", "sgs":"bat-smg", "vro":"fiu-vro", "yue":"zh-yue"
        }

        , "wgQuickSurveysRequireHttps": !1, "wgEnabledQuickSurveys":[ {
            "audience":[], "name":"perceived-performance-survey", "question":"ext-quicksurveys-performance-internal-survey-question", "description":null, "module": "ext.quicksurveys.survey.perceived-performance-survey", "coverage":0, "platforms": {
                "desktop": ["stable"]
            }

            , "privacyPolicy":"ext-quicksurveys-performance-internal-survey-privacy-policy", "type":"internal", "answers":["ext-quicksurveys-example-internal-survey-answer-positive", "ext-quicksurveys-example-internal-survey-answer-neutral", "ext-quicksurveys-example-internal-survey-answer-negative"], "shuffleAnswersDisplay": !0
        }

        ], "wgCentralNoticeActiveBannerDispatcher":"//meta.wikimedia.org/w/index.php?title=Special:BannerLoader", "wgCentralSelectedBannerDispatcher":"//meta.wikimedia.org/w/index.php?title=Special:BannerLoader", "wgCentralBannerRecorder":"//en.wikipedia.org/beacon/impression", "wgCentralNoticeSampleRate":0.01, "wgCentralNoticeImpressionEventSampleRate":0.01, "wgNoticeNumberOfBuckets":4, "wgNoticeBucketExpiry":7, "wgNoticeNumberOfControllerBuckets":2, "wgNoticeCookieDurations": {
            "close": 604800, "donate":21600000
        }

        , "wgNoticeHideUrls":["//en.wikipedia.org/w/index.php?title=Special:HideBanners",
        "//meta.wikimedia.org/w/index.php?title=Special:HideBanners", "//commons.wikimedia.org/w/index.php?title=Special:HideBanners", "//species.wikimedia.org/w/index.php?title=Special:HideBanners", "//en.wikibooks.org/w/index.php?title=Special:HideBanners", "//en.wikiquote.org/w/index.php?title=Special:HideBanners", "//en.wikisource.org/w/index.php?title=Special:HideBanners", "//en.wikinews.org/w/index.php?title=Special:HideBanners", "//en.wikiversity.org/w/index.php?title=Special:HideBanners", "//www.mediawiki.org/w/index.php?title=Special:HideBanners"], "wgCentralNoticePerCampaignBucketExtension":30
    }

    );

    mw.config.set(window.RLCONF|| {}

    );

    mw.loader.state(window.RLSTATE|| {}

    );
    mw.loader.load(window.RLPAGEMODULES||[]);
    var queue=window.RLQ;
    window.RLQ=[];

    RLQ.push=function(fn) {
        if(typeof fn==='function') {
            fn();
        }

        else {
            RLQ[RLQ.length]=fn;
        }
    }

    ;

    while(queue&&queue[0]) {
        RLQ.push(queue.shift());
    }

    window.NORLQ= {
        push:function() {}
    }

    ;
}

());
}