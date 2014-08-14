module.exports = (grunt) ->
  grunt.initConfig(
    pkg: grunt.file.readJSON('package.json')
    project:
      app: 'webapp'
      asset_path:  '<%= project.app %>/assets'
      css_path:    '<%= project.asset_path %>/css'
      less_path:   'extras/dev/assets/less'
      js_path:     '<%= project.asset_path %>/js'
      coffee_path: 'extras/dev/assets/coffee'
    less:
      app:
        files:
          '<%= project.css_path %>/thinkup.css': '<%= project.less_path %>/thinkup.less'
    coffee:
      app:
        files: [
          '<%= project.js_path %>/thinkup.js':'<%= project.coffee_path %>/thinkup.coffee'
        ]
    premailer:
      simple:
        files: 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl': ['extras/dev/precompiledtemplates/email/_email.insights_html.tpl']
    watch:
      email:
        files: 'extras/dev/precompiledtemplates/email/*'
        tasks: ['html_email']
      css:
        files: '<%= project.less_path %>/*'
        tasks: ['less']
      js:
        files: '<%= project.coffee_path %>/*'
        tasks: ['coffee']
  )
  grunt.loadNpmTasks('grunt-contrib-watch')
  grunt.loadNpmTasks('grunt-contrib-less')
  grunt.loadNpmTasks('grunt-contrib-coffee')
  grunt.loadNpmTasks('grunt-premailer')

  grunt.registerTask('fixstyles', 'This fixes the stuff premailer breaks', ->
    html = grunt.file.read 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl'
    html = html.replace(/123456/g,'{$color}').replace(/654321/g,'{$color_dark}').replace(/ABCDEF/g,'{$color_light}')
    html = html.replace('<style type="text/css">','<style type="text/css">{literal}')
    html = html.replace('</style>','{/literal}</style>')
    grunt.file.write 'webapp/plugins/insightsgenerator/view/_email.insights_html.tpl', html
  )
  grunt.registerTask('default', ['premailer', 'fixstyles'])
  grunt.registerTask('html_email', ['premailer', 'fixstyles'])