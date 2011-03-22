package async;

import java.util.ArrayList;
import java.util.Iterator;
import simpletestgui.MainForm;

/**
 * The CommandRunner class is one of the main classes in this program. It handles the
 * running of a group of tests on a separate thread.
 *
 * Pass one of these objects to a thread and run it, like so:
 *
 * Thread t = new Thread(new CommandRunner(<form>, <commands>));
 * t.start();
 *
 * for optimum results.
 * 
 * @author Sam Rose <samwho@lbak.co.uk>
 */
public class CommandRunner implements Runnable {

    private ArrayList<RunCommand> commands;
    private MainForm owner;
    private static final Object lock = new Object();
    private static boolean stop = false;

    /**
     * This is the constructor for the CommandRunner class. The CommandRunner is designed
     * to run a number of test methods from a given file on a separate thread.
     * @param owner The frame that is executing this command.
     * @param commands An ArrayList of RunCommand objects to run.
     */
    public CommandRunner(MainForm owner, ArrayList<RunCommand> commands) {
        this.commands = commands;
        this.owner = owner;
    }

    /**
     * Signals the CommandRunner to stop. The command runner will complete the
     * current test method that it is on and then it will exit. This is to stop
     * any unpredictable results from happening when cancelling a test mid way
     * through.
     */
    public static void stop() {
        stop = true;
    }

    public void run() {
        synchronized (lock) {
            int noCommands = commands.size();
            int commandsRun = 0;

            this.owner.getRunButton().setEnabled(false);
            this.owner.getProgressBar().setMaximum(noCommands);
            this.owner.getProgressBar().setValue(0);

            Iterator<RunCommand> iter = this.commands.iterator();
            while (iter.hasNext() && !stop) {
                RunCommand r = iter.next();
                this.owner.getProgressBar().setString("Running " + r.getMethod() + "...");
                this.owner.getProgressBar().setStringPainted(true);
                r.run();
                this.owner.getProgressBar().setValue(++commandsRun);
            }

            if (stop) {
                this.owner.getProgressBar().setValue(0);
                this.owner.getProgressBar().setString("Cancelled.");
            } else {
                this.owner.getProgressBar().setString("Completed.");
            }

            // reset the stop variable
            stop = false;

            // reset the button states
            this.owner.getRunButton().setEnabled(true);
            this.owner.getCancelButton().setEnabled(false);
        }
    }
}
