package async;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import simpletestgui.MainForm;

/**
 * This class exists to execute a test and output its results to the
 * designated output text box on the MainForm class.
 *
 * This class is designed to be run as part of a CommandRunner and it is
 * not recommended to use it on its own without knowing how it works.
 * 
 * @author Sam Rose <samwho@lbak.co.uk>
 */
public class RunCommand implements Runnable {

    private String command;
    private String[] env;
    private MainForm owner;

    public RunCommand(MainForm owner, String command, String[] env) {
        this.owner = owner;
        this.command = command;
        this.env = env;

        System.out.println("Running command: " + this.command);
        System.out.println("With env: " + this.env[0]);
    }

    public String getCommand() {
        return command;
    }

    public String getMethod() {
        return env[0];
    }

    public void run() {
        try {
            Process p = Runtime.getRuntime().exec(this.command, this.env);
            BufferedReader br = new BufferedReader(new InputStreamReader(p.getInputStream()));

            String line = br.readLine();

            while (line != null) {
                owner.getTestOutput().append(line + "\n");
                owner.getTestOutput().setCaretPosition(owner.getTestOutput().getText().length());
                line = br.readLine();
            }

            br.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
