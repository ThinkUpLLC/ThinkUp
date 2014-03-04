module.exports = (grunt) ->
  grunt.initConfig(
    pkg: grunt.file.readJSON('package.json')
    # banner: '/*! <%= pkg.title || pkg.name %> - ' +
    #   '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
    #   '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %> */\n'
    # # Task configuration.
    # project:
    #   app: 'webapp'
    #   assets: '<%= project.app %>/assets'
    #   css: ['<%= project.assets %>/css/*.less']
    #   js: ['<%= project.assets %>/js/src/*.coffee']
    premailer:
      simple:
        files: 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl': ['extras/dev/precompiledtemplates/email/_email.insights_html.tpl']
    watch:
      files: 'extras/dev/precompiledtemplates/email/*'
      tasks: ['html_email']
  )
  # These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-watch')
  grunt.loadNpmTasks('grunt-premailer')
  # grunt.loadNpmTasks('grunt-contrib-concat');
  # grunt.loadNpmTasks('grunt-contrib-jshint');
  # grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('fixstyles', 'This fixes the stuff premailer breaks', ->
    html = grunt.file.read 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl'
    html = html.replace(/123456/g,'{$color}').replace(/654321/g,'{$color_dark}')
    html = html.replace('<style type="text/css">','<style type="text/css">{literal}')
    html = html.replace('</style>','{/literal}</style>')
    grunt.file.write 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl', html
  )
  grunt.registerTask('default', ['premailer'])
  grunt.registerTask('html_email', ['premailer', 'fixstyles'])